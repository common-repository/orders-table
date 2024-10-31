/**
* The plugin "Orders Table" scripts for admin panel.
*/

jQuery(function($){
	var $wrap = $('.ot'),
		$extra = $wrap.find('.extra');
	
	$('.tabs-buttons').on('click', '.tabs-button:not(.active)', function() {
		$(this)
		.addClass('active').siblings().removeClass('active')
		.closest('.tabs').find('.tabs-content').removeClass('active').eq($(this).index()).addClass('active');
	});
	
	$(function() {
		if ($(window).width() > 1000) {
			$extra.find('.extra-fields').sortable({
				handle: '.move-field',
				placeholder: 'portret-placeholder',
				sort: function(event, ui) {
					var h = $(ui.item).height();
					$(ui.placeholder).css('height', h);
				},	
				beforeStop: function() {
					census();
				}
			});
		}
	});
	
	function census() {
		$extra.find('fieldset:not(.ui-sortable-placeholder)').each(function(index) {
			var num = +index + 1,
				rename_class = $(this).attr('class').replace(/\d+/, num),
				replace_legend = $(this).find('legend').text().replace(/\d+/, num);
			
			$(this).attr('class', rename_class);
			$(this).find('legend').text(replace_legend);
			
			$(this).find('*[name]').each(function() {
				var replace_name = $(this).attr('name').replace(/\d+/, num);
				
				$(this).attr('name', replace_name);
			});
		});
	}
	
	function sortable_on() {
		if ($(window).width() > 1000) {
			if ($extra.find('fieldset').length > 1) {
				$extra.find('.extra-fields').sortable('enable');
			} else {
				if ($extra.find('.extra-fields').hasClass('ui-sortable')) {
					$extra.find('.extra-fields').sortable('disable');
				}
			}
		}
	}
	
	function view_manage() {
		if ($extra.find('fieldset').length <= 1) {	
			$extra.find('.delete-field').hide();
			$extra.find('.move-field').hide();
		} else {
			if ($(window).width() > 1000) {
				$extra.find('.move-field').show();
			}
			
			$extra.find('.delete-field').show();
		}
	}	

	
	$(function() {
		sortable_on();
		view_manage();
	});
	
	$wrap.delegate('.add-field', 'click', function(e) {
		e.preventDefault();
		
		var block = $extra.find('fieldset').eq(-1).clone();	

		block.find('input[type="text"]').val('');
		block.find('select option').first().attr('selected', 'selected');
		block.find('input[type="checkbox"]').removeAttr('checked');
		
		$(this).before(block);
		
		census();
		sortable_on();
		view_manage();
	});	
	
	$wrap.delegate('.delete-field', 'click', function(e) {
		e.preventDefault();

		$(this).parents().closest('fieldset').remove();
		
		census();
		sortable_on();
		view_manage();
	});

	
	function input_to_select_required() {
		var select = $(this).parents().closest('fieldset').find('select');
		
		!$(this).val() == ''?select.attr('required', 'required'):select.removeAttr('required');
	}
	
	function select_to_input_required() {
		var name = $(this).attr('name'),
			input = $(this).parents().closest('fieldset').find('input[name*="field-name"]');

		$('option:nth-child(1)').index($('select[name="' + name + '"] option:selected')) < 0?input.attr('required', 'required'):input.removeAttr('required');
	}
	
	function change_text_checkbox() {
		var label = $(this).siblings().closest('span'),
			text = $(this).is(':checked')?change_text.enable:change_text.disable;
		
		label.text(text);
	}

	$extra.find('fieldset input[name*="field-name"]').each(input_to_select_required);
	$extra.delegate('input[name*="field-name"]', 'input keyup', input_to_select_required);
	
	$extra.find('fieldset select').each(select_to_input_required);
	$extra.delegate('select', 'input keyup', select_to_input_required);
	
	$wrap.find('input[type="checkbox"]').each(change_text_checkbox);
	$wrap.delegate('input[type="checkbox"]', 'onload change', change_text_checkbox);
	
	
	$(document).delegate('input[name="submit"]', 'click', function() {
		var $form = $(this).parents().closest('form');
		
		$form.find('input[required]').each(function() {
			if (!$(this)[0].checkValidity()) {
				$(this)[0].reportValidity();
			}
		});
	});	
});