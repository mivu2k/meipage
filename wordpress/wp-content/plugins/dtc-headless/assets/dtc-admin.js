/* Admin form helpers: media pickers and repeater rows for DTC meta boxes. */
jQuery(function ($) {
  // Media picker (single file or multiple images)
  $(document).on('click', '.dtc-media-btn', function (e) {
    e.preventDefault();
    var wrap = $(this).closest('.dtc-media-field');
    var multi = wrap.data('multi') == 1;
    var frame = wp.media({ multiple: multi });
    frame.on('select', function () {
      var sel = frame.state().get('selection').toJSON();
      if (multi) {
        var ids = wrap.find('input[type=hidden]').val();
        ids = ids ? ids.split(',') : [];
        sel.forEach(function (a) {
          if (ids.indexOf(String(a.id)) !== -1) return;
          ids.push(String(a.id));
          var thumb = a.sizes && a.sizes.thumbnail ? '<img src="' + a.sizes.thumbnail.url + '">' : a.filename;
          wrap.find('.dtc-media-list').append('<li data-id="' + a.id + '">' + thumb + ' <a href="#" class="dtc-media-remove">×</a></li>');
        });
        wrap.find('input[type=hidden]').val(ids.join(','));
      } else {
        var a = sel[0];
        wrap.find('input[type=hidden]').val(a.id);
        var thumb = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.icon;
        if (wrap.hasClass('dtc-media-cell') && thumb) {
          wrap.find('.dtc-media-name').html('<img src="' + thumb + '" style="width:36px;height:36px;object-fit:cover;vertical-align:middle;border-radius:4px">');
        } else {
          wrap.find('.dtc-media-name').text(a.filename || a.title);
        }
      }
    });
    frame.open();
  });

  $(document).on('click', '.dtc-media-remove', function (e) {
    e.preventDefault();
    var li = $(this).closest('li');
    var wrap = li.closest('.dtc-media-field');
    li.remove();
    var ids = wrap.find('.dtc-media-list li').map(function () { return $(this).data('id'); }).get();
    wrap.find('input[type=hidden]').val(ids.join(','));
  });

  // Repeater rows
  $(document).on('click', '.dtc-rep-add', function (e) {
    e.preventDefault();
    var rep = $(this).closest('.dtc-repeater');
    var html = rep.find('.dtc-rep-template').html();
    var index = Date.now(); // unique index; PHP re-indexes on save
    rep.find('tbody').append('<tr>' + html.replace(/__i__/g, index) + '</tr>');
  });

  $(document).on('click', '.dtc-rep-remove', function (e) {
    e.preventDefault();
    $(this).closest('tr').remove();
  });
});
