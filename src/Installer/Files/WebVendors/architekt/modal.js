var ArchitektModal = {

    display: function (response) {

        let config = {
            title: response.content.title,
            html: response.content.html,
            //icon: 'warning',
            showConfirmButton: response.confirm.display,
            confirmButtonText: response.confirm.text,
            confirmButtonColor: this.convertColorClass(response.confirm.class),
            showCancelButton: response.cancel.display,
            cancelButtonText: response.cancel.text,
            cancelButtonColor: this.convertColorClass(response.cancel.class),
        }

        let width = this.convertWidth(response.width)
        if (width) {
            config.width = width;
        }

        if (response.action.isForm) {
            config.focusConfirm = false;
            config.preConfirm = () => {
                FormManager.sendForm(
                    $(SweetAlert.getContainer()).find('form').eq(0),
                    response.action.url,
                    {
                        onSuccess: () => {
                            SweetAlert.close();
                        }
                    }
                );
                return false;
            }
        }

        else{
            config.focusConfirm = false;
            config.preConfirm = () => {
                PageManager.replaceContent(response.action.url);
            }
        }

        SweetAlert.fire(config);


        if (response.action.isForm) {
            $(SweetAlert.getContainer()).find('form').attr('action',response.action.url).attr('method','post');
        }
    },
    convertWidth: function (tag) {
        if (tag === "full") {
            return '100%';
        }
        if (tag === "medium") {
            return '75%';
        }

        return false;
    },
    convertColorClass: function (tag) {
        if (tag === "success") {
            return '#048b3f';
        }
        if (tag === "primary") {
            return '#3085d6';
        }
        if (tag === "error") {
            return '#d33';
        }

        return 'grey';
    },
}