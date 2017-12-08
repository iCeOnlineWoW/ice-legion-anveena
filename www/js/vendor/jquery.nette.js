/**
 * AJAX Nette Framwork plugin for jQuery
 *
 * Developed by: Martin Ubl, iCe Online, http://ice-wow.eu
 * Originally created by: Jan Marek, http://nettephp.com/cs/extras/jquery-ajax
 * License: MIT
 */

jQuery.extend({
    updateSnippet: function(id, html) {
        $("#" + id).html(html);
        initializeAjaxOn("#"+id);
    },

    netteCallback: function(data) {
        // handle redirect request
        if (data.redirect)
            window.location.href = data.redirect;

        // handle snippets contents update
        if (data.snippets)
        {
            for (var i in data.snippets)
                jQuery.updateSnippet(i, data.snippets[i]);
        }

        if (jQuery.isFunction("initAjax"))
            initAjax();
    }
});

jQuery.ajaxSetup({
    success: function (data) {
        jQuery.netteCallback(data);
    },
    dataType: "json"
});

// initialize ajax after document load
$(function() {
    initializeAjax();
});

// general initialization - init ajax on whole document
function initializeAjax()
{
    initializeAjaxOn(document);
}

// initialize ajax on specified DOM subtree
function initializeAjaxOn(place)
{
    $(place).find("a.ajax").click(function(event)
    {
        // store "invoke callback" parameter
        var invokeattr = $(this).attr('data-callback-invoke');

        $.get(this.href).done(function(resp) {
            // if the user needed us to invoke something at the end, do it
            if (invokeattr && invokeattr.length > 0 && typeof window[invokeattr] !== 'undefined')
                window[invokeattr](resp);
        });

        return false;
    });
        
    $(place).find('form').each(function(index, element){
        Nette.initForm(element);
    });

    $(place).find('form.ajax').on('submit', function (event) {
        event.preventDefault();
        if (Nette.validateForm(this))
        {
            // in case of multiple submit buttons, we need to store the submit button name somewhere
            $(this).find('[name=internal__submitted_by]').val($("input[type=submit]:focus").attr('name'));

            // remove "disabled" attribute from disabled form elements (temporary)
            var disabled = $(this).find(':input:disabled').removeAttr('disabled');
            // store "invoke callback" parameter
            var invokeattr = $(this).attr('data-callback-invoke');

            // submit form
            $.post(this.action, $(this).serialize()).done(function(resp) {
                // if the user needed us to invoke something at the end, do it
                if (invokeattr && invokeattr.length > 0 && typeof window[invokeattr] !== 'undefined')
                    window[invokeattr](resp);
            });

            // return "disabled" attribute to those elements
            disabled.attr('disabled','disabled');
        }
        else
            return false;
    });
};