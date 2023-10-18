$(document).ready(function () {
    PageManager.init();
});

var PageManager = {
    mainContentContainer: 'content',
    init: function () {
        if (typeof START_URL !== "undefined") {
            PageManager.replaceContent(START_URL);
        } else {
            if (typeof messageToLaunch != "undefined") {
                MessageManager.display(messageToLaunch.type, messageToLaunch.text);
            }
            PageManager.onContentChange('start');
        }
    },

    onContentChange: function (event,target) {
        FormManager.onContentChange(event);
        ListManager.onContentChange(event);
        LinkManager.onContentChange(event);
        MenuManager.onContentChange(event,target);
        SearchManager.onContentChange(event);
        feather.replace();
    },

    appendContent: function (url, target) {
        target = $(target);
        changeAddress = false;
        $.ajax({
            url: url,
            context: target,
            success: function (data) {
                var stateObj = {foo: "archi"};
                $(this).append(data);
                PageManager.onContentChange('append');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (403 === jqXHR.status) {
                    MessageManager.display('danger', 'Page non accessible');
                    PageManager.reload(jqXHR.responseText);
                } else if (302 === jqXHR.status) {
                    PageManager.reload(jqXHR.responseText);
                } else {
                    MessageManager.display('danger', 'Un problème est survenu');
                }
            }
        });
    },

    replaceContent: function (url, target, changeAddress, event) {
        if (typeof event === "undefined") {
            event = 'replace';
        }
        if (typeof target === "undefined") {
            target = $('#' + PageManager.mainContentContainer);
            changeAddress = true;
        } else {
            target = $(target);
        }
        if (typeof changeAddress === "undefined") {
            changeAddress = false;
        }
        $.ajax({
            url: url,
            context: target,
            success: function (data) {
                var stateObj = {foo: "archi"};
                if (changeAddress) {
                    history.pushState(stateObj, "Architekt", url);
                }
                $(this).html(data);
                PageManager.onContentChange(event,target);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (403 === jqXHR.status) {
                    MessageManager.display('danger', 'Page non accessible');
                    PageManager.reload(jqXHR.responseText);
                } else if (302 === jqXHR.status) {
                    PageManager.reload(jqXHR.responseText);
                } else {
                    MessageManager.display('danger', 'Un problème est survenu');
                }
            }
        });
    },

    reload: function (url) {
        setTimeout(function () {
            document.location.href = url;
        }, 1000);
    }
}

var LinkManager = {
    replace: function () {
        $('a[href]:not([bind])').each(function () {
            let to = $(this).attr('href');
            $(this)
                .attr('bind', 1)
                .bind('click', function (event) {
                    let response = LinkManager.click($(this), event);
                    event.stopPropagation();
                    return response;
                })
                .data('url', to);
        });
    },

    onContentChange: function () {
        this.replace();
    },

    click: function (on, event) {
        let lock = on.attr('lock');
        if (lock) return true;

        if (typeof event !== "undefined") {
            if (event.ctrlKey || event.shiftKey) {
                return true;
            }
        }
        let real = on.attr('real');

        if (typeof real !== "undefined") {
            return true;
        }

        let confirmText = on.attr('confirm');

        if(typeof confirmText !== "undefined"){
            if(!confirm(confirmText)){
                return false;
            }
        }

        let eventType = on.attr('eventType');

        if ("action" === eventType) {
            this.action(on);
            return false;
        }

        if ("modal" === eventType) {
            this.modal(on);
            return false;
        }

        if ("change" === eventType) {
            this.change(on);
            return false;
        }

        if ("append" === eventType) {
            this.append(on);
            return false;
        }

        this.replaceContent(on);
        return false;
    },
    append: function (on) {
        let target = on.data('target');
        let url = on.data('url');
        target = $('#' + target);
        PageManager.appendContent(url, target);
    },
    modal: function (on) {
        console.log('call modal');
        let method = on.data('method');
        let url = on.data('url');
        if ("POST" === method) {
            $.post({
                url: url
            }).done(function (response) {
                ModalManager.off();
                eval(response);
            });
        } else {

            $.ajax({
                url: url,
                success: function (response) {
                    ArchitektModal.display(response);
                    PageManager.onContentChange('modal_on');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if (403 === jqXHR.status) {
                        MessageManager.display('danger', 'Page non accessible');
                    } else if (404 === jqXHR.status) {
                        MessageManager.display('danger', 'Page inexistante');
                    } else {
                        MessageManager.display('danger', 'Un problème est survenu - modal(' + jqXHR.status + ')');
                    }
                },
                dataType: 'json'
            });
            return false;
        }
    },

    action: function (on) {
        let url = on.data('url');

        $.post({
            url: url,
            success: function (response) {
                ResponseManager.parse(response);
                FormManager.enable();
                PageManager.onContentChange('action');

            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus);
                if (403 === jqXHR.status) {
                    MessageManager.display('danger', 'Page non accessible');
                } else if (404 === jqXHR.status) {
                    MessageManager.display('danger', 'Page inexistante');
                } else if (302 === jqXHR.status) {
                    PageManager.reload(textStatus);
                } else {
                    MessageManager.display('danger', 'Un problème est survenu - action (' + jqXHR.status + ')');
                }
                FormManager.enable();
            },
            dataType: 'json'
        });
    },
    change: function (on) {
        let url = on.data('url');

        $.post({
            url: url,
            success: function (response) {
                eval(response);
                PageManager.onContentChange('change');

            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (403 === jqXHR.status) {
                    MessageManager.display('danger', 'Page non accessible');
                } else if (404 === jqXHR.status) {
                    MessageManager.display('danger', 'Page inexistante');
                } else {
                    MessageManager.display('danger', 'Un problème est survenu - change (' + jqXHR.status + ')');
                }
            }
        });
    },

    replaceContent: function (on) {
        let target = on.data('target');
        let url = on.data('url');

        if (typeof target !== "undefined") {
            target = $('#' + target);
        }

        PageManager.replaceContent(url, target);
    }
}

var MessageManager = {
    timeout: 4,
    timeoutHandler: false,
    display: function (type, message) {
        if (this.timeoutHandler) {
            clearTimeout(this.timeoutHandler);
            MessageManager.close();
        }
        this.container().find('.message-content').html(message);
        this.container().find('.alert').addClass('alert-fill-' + type);
        this.container().toggle(true);

        this.timeoutHandler = setTimeout(function () {
            MessageManager.close();
        }, this.timeout * 1000);
    },
    close: function () {
        this.container().toggle(false);
        this.clearType();
    },
    container: function () {
        return $('#message');
    },
    clearType: function () {
        this.container().find('.alert').removeClass('alert-fill-danger alert-fill-success alert-fill-warning');
    }
}

var MenuManager = {

    manageVerticalSidebarSelection : function(){
        uri = document.location.href.replace(URL_APP,'');
        uriSplit = uri.split('#');
        uri = uriSplit[0];
        $('ul.autoactivate .nav a[href]')
            .removeClass('active')
            .each(function(){
                if($(this).attr('href') === uri){
                    $(this).addClass('active');
                    $(this).parents('.collapse').addClass('show');
                    $(this).parents('.nav-item').addClass('active');
                }
            });
    },
    onContentChange: function (event,target) {
        this.manageVerticalSidebarSelection();

        return ;



        if(event === 'modal_on' || event === 'modal_success' || event === 'action' || event === 'append'){
            return ;
        }
        if (typeof target !=="undefined" && target !== true && $(target).attr('id') !== PageManager.mainContentContainer) {
            return ;
        }

        $('#menu-container').removeClass("border-home border-equipment border-intervention");

        if (typeof MENU_SELECTED === 'undefined' || MENU_SELECTED === '') {
            $('#menu-container').addClass("border-home");
            $('.menu').hide();
            return true;
        }
        $('.menu').hide();


        $('#' + MENU_SELECTED).show();
        $('#menu-container').addClass("border-" + MENU_SELECTED);
        MENU_SELECTED = '';

        $('.nav-link').bind('click', function (e) {
            e.preventDefault();
            $(this).parents('.nav-tabs').find('.nav-link').removeClass('active');
            $(this).addClass('active');
        }).css('cursor', 'pointer');

        $('ul.nav').each(function () {
            if ($(this).data('type') === 'autoload') {
                if ($(this).children('.nav-link.active').length === 0) {
                    $(this).children().find('.nav-link').first().trigger('click');
                }
                $(this).data('type', '');
            }
        })
    }
}

var ListManager = {

    onContentChange: function () {
        $('table.list').each(function () {
            let datatable = $(this).DataTable({
                order: [],
                language: {
                    url: URL_CDN+'/vendors/datatables.net/datatables.fr.json',
                },
                columnDefs: [ {
                    targets: 'no-sort',
                    orderable: false,
                } ],
                retrieve: true
            });

            $(this).find('tr[itemId] td:not([customAction])').unbind('click').on('click', {datatable:datatable}, function (e) {
                if ($(this).parent('tr').attr('action')) {
                    PageManager.replaceContent($(this).parent('tr').attr('action'));
                }
                if ($(this).parent('tr').attr('modal')) {
                    LinkManager.click($(this).parent('tr'),'modal');
                }
                if ($(this).parent('tr').attr('appendUrl')) {
                    PageManager.appendContent($(this).parent('tr').attr('appendUrl'),$(this).parent('tr').attr('appendTarget'));
                }
                if ($(this).parent('tr').attr('select')) {
                    $('#' + $(this).parent('tr').attr('select')).val($(this).parent('tr').attr('itemId')).trigger('change');
                }

                if ($(this).parent('tr').attr('removeRow') === '1') {
                    e.data.datatable.row($(this).parent('tr')).remove().draw();
                }
            }).css('cursor', 'pointer');
        })

    }
}

var FormManager = {
    bind: function () {
       /* $('form:not([bind])').each(function () {
            let buttons = $(this).find('button[role=submit]');
            if (buttons.length > 0) {
                $(this).attr('bind', 1);
                buttons.bind('click', {form: this}, function (e) {
                    console.log($(this)[0]);
                    return false;
                    FormManager.submit($(this), $(this).attr('action'));
                });
            }
            return false;
        })*/
        $('form:not([bind])')
            .attr('bind', 1)
            .on('submit', function () {
                FormManager.submit($(this), $(this).attr('action'));
                return false;
            });

    },

    onContentChange: function () {
        FormManager.enable();
        this.bind();
    },

    submit: function (form, action) {
        this.disable(form);
        FormManager.sendForm(form, action);
    },
    sendForm: function (form, action,  callbacks) {
        $.ajax({
            type: "POST",
            url: action,
            data: new FormData(form[0]),
            processData: false,
            contentType: false,
            context: form,
            dataType: 'json',
            success: function (data) {
                let success = FormManager.onResponse($(this), data);

                if(typeof callbacks !== "undefined")
                {
                    if(success && typeof callbacks.onSuccess != "undefined") {
                        callbacks.onSuccess();
                    }
                    if(!success && typeof callbacks.onFailure != "undefined") {
                        callbacks.onFailure();
                    }
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (403 === jqXHR.status) {
                    PageManager.reload('/Redirect/error/403');
                } else if (404 === jqXHR.status) {
                    PageManager.reload('/Redirect/error/404');
                } else {
                    MessageManager.display('danger', 'Un problème est survenu (' + jqXHR.status + ')');
                }
                FormManager.enable(this);

                if(typeof callbacks !== "undefined" && typeof callbacks.onFailure != "undefined") {
                    callbacks.onFailure();
                }

                return false;
            },
        });
    },


    onResponse: function (form, response) {
        if (response.success) {
            this.onValidationSuccess(form, response);
            return true;
        }

        this.onValidationError(form, response);
        return false;

    },

    onValidationSuccess: function (form, response) {
        ResponseManager.parse(response, 'modal_success');
    },

    onValidationError: function (form, response) {

        if (typeof response.reloadTo != "undefined") {
            PageManager.reload(response.reloadTo);
        }
        let mainMessage = "<b>"+response.message+"</b>";
        for (var i in response.details) {
            let constraintDetails = response.details[i];

            for (var j in constraintDetails.fields) {
                this.manageFieldContraints(constraintDetails.fields[j], constraintDetails.success, constraintDetails.message);
                if( !constraintDetails.success){
                    mainMessage+="<li>"+constraintDetails.message+"</li>";
                }
            }
        }

        MessageManager.display('danger', mainMessage);
        this.enable(form);
    },

    manageFieldContraints: function (field, isSuccess, message) {

        let fieldItem = $("#" + field + "-input");

        fieldItem.attr('title', '');
        fieldItem.removeClass('is-valid is-invalid');

        if (true === isSuccess) {
            fieldItem.addClass('is-valid');
            $("#" + field + "-group .invalid-feedback").hide();
            $("#" + field + "-group .valid-feedback").show().html(message);
        } else if (false === isSuccess) {
            fieldItem.addClass('is-invalid');
            $("#" + field + "-group .valid-feedback").hide();
            $("#" + field + "-group .invalid-feedback").show().html(message);
            fieldItem.attr('title', message);
        } else if (null === isSuccess) {
            $("#" + field + "-group .valid-feedback").hide();
            $("#" + field + "-group .invalid-feedback").hide();
        }

        if (!fieldItem.data('bindChange')) {
            fieldItem.bind('keypress change', function () {
                $(this).attr('title', '');
                $(this).removeClass('is-valid is-invalid');
                let group = '#' + ($(this).attr('id').replace('-input', '')) + '-group';
                $(group).find('[class*=valid]').hide();
            }).data('bindChange', true);
        }
    },


    disable: function (form) {
        $('body').css('cursor', 'wait');

        if (typeof form !== "undefined") {
            if ($(form).parents('#modal').length) {
                $('#modal').find('a.confirm').each(function () {
                    $(this).attr('oldContent', $(this).html());
                    $(this).html('En cours...');
                }).prop('disabled', true);
            } else {
                $(form).find('[role=submit]').each(function () {
                    $(this).attr('oldContent', $(this).html());
                    $(this).html('En cours...');
                }).prop('disabled', true);

                $(form).find('[type=submit]').each(function () {
                    $(this).attr('oldContent', $(this).html());
                    $(this).html('En cours...');
                }).prop('disabled', true);
            }
        }
    },

    enable: function (form) {
        $('body').css('cursor', 'auto');

        if(typeof form === "undefined"){
            $('button[role=submit]').each(function () {
                $(this).html($(this).attr('oldContent'));
            }).prop('disabled', false);

            return ;
        }

        if ($(form).parents('#modal').length) {
            $('#modal').find('a.confirm').each(function () {
                $(this).html($(this).attr('oldContent'));
            }).prop('disabled', false);
        } else {
            $(form).find('[role=submit]').each(function () {
                $(this).html($(this).attr('oldContent'));
            }).prop('disabled', false);

            $(form).find('[type=submit]').each(function () {
                $(this).html($(this).attr('oldContent'));
            }).prop('disabled', false);
        }
    }
}

var ResponseManager =
    {
        parse: function (response, event) {

            if (typeof response.reloadTo != "undefined") {
                PageManager.reload(response.reloadTo);
            } else {
                if (typeof response.message != "undefined") {
                    MessageManager.display("success", response.message);
                }
                if (typeof response.returnTo != "undefined") {
                    if (typeof response.returnTarget != "undefined") {
                        PageManager.replaceContent(response.returnTo, response.returnTarget);
                    } else {
                        PageManager.replaceContent(response.returnTo);
                    }
                }
            }
        }
    }


var SearchManager = {
    onContentChange: function(event){
        $('.input-filter')
            .unbind('change')
            .on('change',function(e){
                e.stopPropagation();
                $(this).parents('form').trigger('submit');
            });
    }
}
