var validatorConfig = {
    'lang' : '<?php echo addslashes($languageCode); ?>',
    //'errorClass' : 'ipmControlError',
    'messageClass' : 'ipmErrorMessage',
    //'position' : 'bottom left',
    //'offset' : [-3, 0],
    'onFail' : function(e, errors) {
        $.each(errors, function() {
            var err = this;
            var $control = this.input;
            $control.parents('.form-group')
                .addClass('has-error')
                .find('.help-error').html(this.messages.join(' '));
            if (this.messages.join('') == '') {
                //hide error if no error text present
                $control.parents('.form-group').find('.help-error').hide()
            } else {
                $control.parents('.form-group').find('.help-error').show()
            }
        });
    },
    'onSuccess' : function(e, valids) {
        $.each(valids, function() {
            var $control = $(this);
            $control.parents('.form-group').removeClass('has-error');
        });
    }
};


$.tools.validator.localize('<?php echo addslashes($languageCode); ?>', {
    '*'           : '<?php echo addslashes($this->par('Form.unknown')) ?>',
    ':email'      : '<?php echo addslashes($this->par('Form.email')) ?>',
    ':number'     : '<?php echo addslashes($this->par('Form.number')) ?>',
    ':url'        : '<?php echo addslashes($this->par('Form.url')) ?>',
    '[max]'       : '<?php echo addslashes($this->par('Form.max')) ?>',
    '[min]'       : '<?php echo addslashes($this->par('Form.min')) ?>',
    '[required]'  : '<?php echo addslashes($this->par('Form.required')) ?>'
});
