jQuery(function ($) {
  const $list = $('#nexora-gallery-list');
  const $input = $('#nexora_gallery_ids');

  function syncGallery() {
    if (!$input.length) return;
    const ids = $list.children().map(function () {
      return $(this).data('id');
    }).get();
    $input.val(ids.join(','));
  }

  if ($list.length && $.fn.sortable) {
    $list.sortable({
      items: '> li',
      tolerance: 'pointer',
      update: syncGallery,
    });
  }

  $(document).on('click', '#nexora-gallery-add', function () {
    const frame = wp.media({
      title: window.NexoraAdmin?.galleryTitle || 'Project gallery',
      multiple: true,
      library: { type: 'image' },
    });

    frame.on('select', function () {
      frame.state().get('selection').each(function (attachment) {
        const image = attachment.toJSON();
        if ($list.find(`[data-id="${image.id}"]`).length) return;
        const thumb = image.sizes?.thumbnail?.url || image.url;
        $list.append(
          `<li data-id="${image.id}"><img src="${thumb}" alt=""><span class="nexora-gallery-id">#${image.id}</span><button type="button" class="button-link-delete" aria-label="${window.NexoraAdmin?.removeLabel || 'Remove'}">×</button></li>`
        );
      });
      syncGallery();
    });

    frame.open();
  });

  $(document).on('click', '.nexora-gallery-admin .button-link-delete', function () {
    $(this).closest('li').remove();
    syncGallery();
  });

  $(document).on('click', '[data-media-select]', function () {
    const $field = $(this).closest('[data-media-field]');
    const frame = wp.media({
      title: window.NexoraAdmin?.mediaTitle || 'Select image',
      multiple: false,
      library: { type: 'image' },
    });

    frame.on('select', function () {
      const image = frame.state().get('selection').first().toJSON();
      const preview = image.sizes?.medium?.url || image.sizes?.thumbnail?.url || image.url;
      $field.find('[data-media-input]').val(image.id);
      $field.find('[data-media-preview]').html(`<img src="${preview}" alt="">`);
      $field.find('[data-media-remove]').prop('hidden', false);
    });

    frame.open();
  });

  $(document).on('click', '[data-media-remove]', function () {
    const $field = $(this).closest('[data-media-field]');
    $field.find('[data-media-input]').val('');
    $field.find('[data-media-preview]').empty();
    $(this).prop('hidden', true);
  });
});

jQuery(function($){
  $(document).on('submit','form[data-nexora-confirm]',function(e){
    const message=$(this).data('nexora-confirm');
    if(message && !window.confirm(message)){ e.preventDefault(); }
  });
});
