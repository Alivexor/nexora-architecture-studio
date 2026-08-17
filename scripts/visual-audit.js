/* Local visual QA runner for the live WordPress site. Run with NODE_PATH pointing at Playwright. */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const root = 'http://localhost/nexora/';
const chrome = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const out = process.argv[2] || 'docs/visual-audit/before';
const topOnly = process.env.NEXORA_AUDIT_TOP === '1';
const allViewports = [{ name: '1920x1080', width: 1920, height: 1080 }, { name: '1440x900', width: 1440, height: 900 }, { name: '1366x768', width: 1366, height: 768 }, { name: '1024x768', width: 1024, height: 768 }, { name: '768x1024', width: 768, height: 1024 }, { name: '430x932', width: 430, height: 932 }, { name: '390x844', width: 390, height: 844 }, { name: '360x800', width: 360, height: 800 }];
const requested = (process.env.NEXORA_AUDIT_VIEWPORTS || '').split(',').filter(Boolean);
const viewports = requested.length ? allViewports.filter(v => requested.includes(v.name)) : allViewports;
const report = { pages: [], console: [], failed: [], overflow: [] };
const safe = s => s.replace(/^https?:\/\/[^/]+\/nexora\/?/, '').replace(/^\/|\/$/g, '').replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '') || 'home';
async function settle(page) {
  await page.waitForTimeout(500);
  // Native lazy-loading only starts after an element approaches the viewport.
  // Visit each image briefly so the full-page capture records the same assets a real reader receives.
  for (const image of await page.locator('img[loading="lazy"]').all()) {
    await image.scrollIntoViewIfNeeded();
    await image.evaluate(img => img.complete || Promise.race([
      new Promise(resolve => { img.addEventListener('load', resolve, { once: true }); img.addEventListener('error', resolve, { once: true }); }),
      new Promise(resolve => setTimeout(resolve, 1200)),
    ]));
  }
  await page.evaluate(async () => {
    for (let y = 0; y < document.documentElement.scrollHeight; y += Math.max(350, innerHeight * .72)) {
      scrollTo(0, y); await new Promise(r => setTimeout(r, 90));
    }
    document.querySelectorAll('.reveal').forEach(el => el.classList.add('is-visible'));
    scrollTo(0, 0); await new Promise(r => setTimeout(r, 900));
  });
}
async function main() {
  fs.mkdirSync(out, { recursive: true });
  const browser = await chromium.launch({ executablePath: chrome, headless: true });
  const seed = await browser.newPage();
  await seed.goto(root, { waitUntil: 'networkidle' });
  const links = await seed.locator('a[href]').evaluateAll((as, base) => [...new Set(as.map(a => a.href).filter(h => h.startsWith(base) && !/wp-admin|wp-login|#/.test(h))) ], root);
  // Include both language home pages and all publicly discoverable first-level content links.
  const auditUrls = process.env.NEXORA_AUDIT_URLS ? process.env.NEXORA_AUDIT_URLS.split('|') : null;
  const urls = auditUrls || [...new Set([root, ...links])].slice(0, 40);
  await seed.close();
  const primary = urls.filter(u => /(?:^|\/)(?:projects?|studio|about|services?|contact|journal|search)(?:\/|$)|nexora_project|nexora_service|post_type|page_id/.test(u)).slice(0, 16);
  const shots = [];
  for (const url of [...new Set([root, ...primary])]) {
    for (const vp of viewports) {
      const page = await browser.newPage({ viewport: vp });
      page.on('console', m => { if (m.type() === 'error') report.console.push({ url, text: m.text() }); });
      page.on('requestfailed', r => report.failed.push({ url, request: r.url(), error: r.failure()?.errorText }));
      const response = await page.goto(url, { waitUntil: 'networkidle', timeout: 30000 });
      if (!topOnly) await settle(page);
      const pageName = safe(url);
      const lang = (await page.locator('html').getAttribute('lang') || '').toLowerCase().startsWith('fa') ? 'fa' : 'en';
      const file = `${lang}-${pageName}-${vp.name}.png`;
      const target = path.join(out, file);
      await page.screenshot({ path: target, fullPage: !topOnly }); shots.push(file);
      const dims = await page.evaluate(() => ({ width: document.documentElement.scrollWidth, viewport: innerWidth, height: document.documentElement.scrollHeight }));
      report.pages.push({ url, lang, viewport: vp.name, status: response?.status(), ...dims });
      if (dims.width > dims.viewport + 1) report.overflow.push({ url, viewport: vp.name, ...dims });
      await page.close();
    }
  }
  fs.writeFileSync(path.join(out, 'runtime-report.json'), JSON.stringify({ ...report, screenshots: shots }, null, 2));
  console.log(JSON.stringify({ screenshots: shots.length, pages: report.pages.length, errors: report.console.length, failed: report.failed.length, overflow: report.overflow.length }, null, 2));
  await browser.close();
}
main().catch(e => { console.error(e); process.exit(1); });
