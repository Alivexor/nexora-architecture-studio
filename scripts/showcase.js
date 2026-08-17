/* Captures curated live-browser evidence for the project handoff. */
const { chromium } = require('playwright');
const fs = require('fs');
const chrome = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const base = 'http://localhost/nexora/';
const pages = [
  ['fa-home-desktop.png', '?page_id=202', { width: 1440, height: 900 }],
  ['en-home-desktop.png', '?page_id=203', { width: 1440, height: 900 }],
  ['fa-home-mobile.png', '?page_id=202', { width: 390, height: 844 }],
  ['en-home-mobile.png', '?page_id=203', { width: 390, height: 844 }],
  ['projects-archive.png', '?post_type=nexora_project', { width: 1440, height: 900 }],
  ['project-case-study.png', '?nexora_project=silent-courtyard-house', { width: 1440, height: 900 }],
  ['studio.png', '?page_id=204', { width: 1440, height: 900 }],
  ['services.png', '?post_type=nexora_service', { width: 1440, height: 900 }],
  ['journal.png', '?page_id=208', { width: 1440, height: 900 }],
  ['contact.png', '?page_id=206', { width: 1440, height: 900 }],
  ['search.png', '?s=light', { width: 1440, height: 900 }],
];
(async () => {
  fs.mkdirSync('docs/showcase', { recursive: true });
  const browser = await chromium.launch({ executablePath: chrome, headless: true });
  for (const [file, query, viewport] of pages) {
    const page = await browser.newPage({ viewport });
    await page.goto(base + query, { waitUntil: 'networkidle' });
    await page.screenshot({ path: 'docs/showcase/' + file });
    await page.close();
  }
  const mobile = await browser.newPage({ viewport: { width: 390, height: 844 } });
  await mobile.goto(base + '?page_id=202', { waitUntil: 'networkidle' });
  await mobile.locator('.menu-toggle').click();
  await mobile.waitForTimeout(500);
  await mobile.screenshot({ path: 'docs/showcase/mobile-navigation.png' });
  await mobile.close();
  const project = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  await project.goto(base + '?nexora_project=brick-courtyard-renovation', { waitUntil: 'networkidle' });
  const gallery = project.locator('.masonry-gallery');
  if (await gallery.count()) { await gallery.scrollIntoViewIfNeeded(); await gallery.screenshot({ path: 'docs/showcase/project-gallery.png' }); }
  const compare = project.locator('[data-before-after]');
  if (await compare.count()) { await compare.scrollIntoViewIfNeeded(); await compare.screenshot({ path: 'docs/showcase/before-after.png' }); }
  await project.close();
  await browser.close();
})().catch(error => { console.error(error); process.exit(1); });
