// IW_Option
function IW_Option($el, $select2) {
  this.element = $el; 
  this.select2 = $select2;
}

IW_Option.prototype.queryOpt = function() {
  jQuery.getJSON(ajaxurl + "?action=" + select2_item.attr('data-src'), {id: $option.val()}, this.onFound.bind(this)); 
}

IW_Option.prototype.onFound = function(data) {
  var select2_item = this.select2;
  var $option = this.element;

  if(data.items[0]) {
      $option.text(data.items[0].text); // update the text that is displayed (and maybe even the value)
      $option.removeData(); // remove any caching data that might be associated

      select2_item.trigger('change'); // notify JavaScript components of possible changes

      console.log(this);
    }
}

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
        data: function (params) {
          return {
            q: params.term, // search term
            page: params.page
          };
        },
        processResults: function (data, params) {
            var more = (params.page * 30) < data.total_count;
            return { results: data.items, more: more };
        },
        cache: true
      },
      placeholder: placeholder,
      allowClear: true,
      escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
      minimumInputLength: 1,
      multiple: is_multiple
    });

    // Initialize vals:
    var init_options = select2_item.children('option');

    for(var h = 0; h < init_options.length; h++) {
      var $option = jQuery(init_options[h]);
      var opt_obj = new IW_Option($option, select2_item);
      
      opt_obj.queryOpt();           
    }

  } else {
    select2_item.select2();
  }
}
