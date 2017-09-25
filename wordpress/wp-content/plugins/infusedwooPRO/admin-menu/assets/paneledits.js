var all_select2 = jQuery('.iw-select2');

for(var i = 0; i < all_select2.length; i++) {
  var select2_item = jQuery(all_select2[i]);
  var is_multiple = jQuery(all_select2[i]).is("[multiple]");
  var placeholder = select2_item.attr('placeholder') ? select2_item.attr('placeholder') : 'Select a ' + select2_item.attr('data-type');

  if(select2_item.attr('data-src')) {
    select2_item.select2({
      ajax: {
        url: ajaxurl + "?action=" + select2_item.attr('data-src'),
        dataType: 'json',
        delay: 250,
        data: function (term,page) {
          return {
            q: term, // search term
            page: page
          };
        },
        results: function (data, page) {
            var more = (page * 30) < data.total_items;
            return { results: data.items, more: more };
        },
        cache: true
      },
      placeholder: placeholder,
      initSelection: function(element, callback) {
          var id = jQuery(element).val();
          var is_multiple = jQuery(element).is("[multiple]");
          var data_type = jQuery(element).attr('data-type');

          if (id !== "" & id != 0) {
              jQuery.ajax(ajaxurl + "?action=" + jQuery(element).attr('data-src') + "&id=" + id, {
                  dataType: "json"
              }).done(function(data) { 
                if(!is_multiple) {
                  if(data.items[0]) {
                    callback(data.items[0]); 
                  } else {
                    callback({'id': id, 'text': data_type + ' # ' + id});
                  }
                } else {
                  if(data.items) {
                     callback(data.items);
                  } else {
                    var data = [];
                    id.split(",").each(function () {
                        data.push({id: this, text: data_type +' # ' + this});
                    });
                    callback(data);
                  }
                }
              }).error(function() {
                if(!is_multiple) {
                  callback({'id': id, 'text': data_type +' # ' + id});
                } else {
                  var data = [];
                  id.split(",").each(function () {
                      data.push({id: this, text: data_type +' # ' + this});
                  });
                  callback(data);
                }
              });
          } else {
            if(jQuery(element).attr('placeholder')) {
              callback({'id': id, 'text': jQuery(element).attr('placeholder') });
            }
          }
      },
      allowClear: true,
      escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
      minimumInputLength: 1,
      multiple: is_multiple
    });
  
    if(select2_item.val() && select2_item.val() != 0) {
      select2_item.select2('val', select2_item.val());
    }
  } else {
    select2_item.select2();
  }
}
