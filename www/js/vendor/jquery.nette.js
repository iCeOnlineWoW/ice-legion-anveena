/**
 * AJAX Nette Framwork plugin for jQuery
 *
 * @copyright  Copyright (c) 2009 Jan Marek
 * @license    MIT
 * @link       http://nettephp.com/cs/extras/jquery-ajax
 * @version    0.1
 */

jQuery.extend({
	updateSnippet: function(id, html) {
		$("#" + id).html(html);
                initializeAjaxInSnippet("#"+id);
	},

	netteCallback: function(data) {
		// redirect
		if (data.redirect) {
			window.location.href = data.redirect;
		}

		// snippets
		if (data.snippets) {
                    for (var i in data.snippets) {
                            jQuery.updateSnippet(i, data.snippets[i]);
                    }
		}
                //initialize ajax in snippets
                if(jQuery.isFunction("initAjax")){
                    initAjax();
                }
	}
});


jQuery.ajaxSetup({
	success: function (data) {
		jQuery.netteCallback(data);
	},

	dataType: "json"
});


//initialize ajax after document load
$(function() {
    initializeAjax();
});

function initOnStartOrRedraw(){
    //confirm dialog - on open, load right href
    $('a.confirmDialog:not(.active)').click(function(){
        $('#'+$(this).data('reveal-id')+' a.continue').prop('href', $(this).data('href'));
        $(this).addClass('.active');
    });
}
    
function initializeAjax(){
    // apply AJAX unobtrusive way
    $(document).on("click","a.ajax", function(event) {
        $.get(this.href);

        // show spinner
        $('<div id="ajax-spinner"></div>').css({
                position: "absolute",
                left: event.pageX + 20,
                top: event.pageY + 40

        }).ajaxStop(function() {
                $(this).remove();

        }).appendTo("body");

        return false;
    });
        
    // odesílání formulářů
    $('form.ajax').on('submit', function (event) {
        event.preventDefault();
        if(Nette.validateForm(this)){
            $(this).find('[name=internal__submitted_by]').val($("input[type=submit]:focus").attr('name'));
            var disabled = $(this).find(':input:disabled').removeAttr('disabled');
            $.post(this.action, $(this).serialize());
            disabled.attr('disabled','disabled');
        }else{
            return false;
        }
    });
    initOnStartOrRedraw();
};
function initializeAjaxInSnippet(place){
	// apply AJAX unobtrusive way
	$(place).find("a.ajax").click(function(event) {
		$.get(this.href);

		// show spinner
		$('<div id="ajax-spinner"></div>').css({
			position: "absolute",
			left: event.pageX + 20,
			top: event.pageY + 40

		}).ajaxStop(function() {
			$(this).remove();

		}).appendTo("body");

		return false;
	});
        
    // odesílání formulářů
    $(place).find('form').each(function(index, element){
        Nette.initForm(element);
    });
    $(place).find('form.ajax').on('submit', function (event) {
        event.preventDefault();
        if(Nette.validateForm(this)){
            $.post(this.action, $(this).serialize());
        }else{
            return false;
        }
    });
    initOnStartOrRedraw();
};