var ArchitektModal = {

    display: function (response) {

        this.append();

        this.container().removeClass('modal-xl', 'modal-lg', 'modal-sm');

        let width = this.convertWidth(response.width);
        if (width) {
            this.container().addClass('modal-' + width);
        }

        this.container().find('.modal-title').html(response.content.title);
        this.container().find('.modal-body').html(response.content.html);

        let footer = this.container().find('.modal-footer');
        let confirm = footer.find('.confirm');
        confirm.unbind('click');

        if (response.confirm.display) {
            confirm.removeClass('btn-danger', 'btn-warning', 'btn-primary', 'btn-info', 'btn-secondary');
            confirm.addClass('btn-' + this.convertColorClass(response.confirm.class));
            confirm.html(response.confirm.text);

            if (response.action.isForm) {
                this.form().attr('action',response.action.url).attr('method','post');
                confirm.bind('click', function (event) {
                    event.stopPropagation();
                    FormManager.sendForm(
                        ArchitektModal.form(),
                        response.action.url
                    );
                }).attr('lock', 1);
            } else {
                confirm.attr('lock', 0);
            }
            confirm.show();
        } else {
            confirm.hide();
        }

        let cancel = footer.find('.cancel');
        if (response.cancel.display) {
            cancel.show();
            cancel.removeClass('btn-danger', 'btn-warning', 'btn-primary', 'btn-info', 'btn-secondary');
            cancel.addClass('btn-' + this.convertColorClass(response.cancel.class));
            cancel.html(response.cancel.text);
        } else {
            cancel.hide();
        }

        this.on();

    },

    append: function () {
        console.log(this.container().length);
        if (this.container().length) {
            return;
        }

        $('body').append('<div class="modal" id="' + this.id + '">\n' +
            '    <div class="modal-dialog  modal-dialog-centered">\n' +
            '        <div class="modal-content">\n' +
            '            <div class="modal-header">\n' +
            '                <h5 class="modal-title">Chargement...</h5>\n' +
            '                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>\n' +
            '            </div>\n' +
            '            <div class="modal-body">\n' +
            '                <p>Chargement...</p>\n' +
            '            </div>\n' +
            '            <div class="modal-footer">\n' +
            '                <button type="button" class="btn btn-secondary cancel" data-bs-dismiss="modal">Annuler</button>\n' +
            '                    <a href="javascript:void(0);"  class="btn btn-primary confirm" eventType="silent">Chargement...</a>\n' +
            '            </div>\n' +
            '        </div>\n' +
            '    </div>\n' +
            '</div>');
    },

    convertWidth: function (tag) {
        if (tag === "full") {
            return 'xl';
        }
        if (tag === "medium") {
            return 'lg';
        }

        return false;
    },
    convertColorClass: function (tag) {
        if (tag === "error") {
            return 'danger';
        }

        return tag;
    },

    container: function () {
        return $('#' + this.id);
    },
    content: function () {
        return this.container().find('.modal-content');
    },
    form: function () {
        return this.container().find('.modal-content').find('form');
    },
    off: function () {
        this.container().modal('hide');
    },
    on: function () {
        this.container().modal('show');
    }
}