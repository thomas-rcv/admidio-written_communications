<form {foreach $attributes as $attribute}
        {$attribute@key}="{$attribute}"
    {/foreach}>
    <div class="admidio-form-required-notice"><span>{$l10n->get('SYS_REQUIRED_INPUT')}</span></div>

    {include 'sys-template-parts/form.input.tpl' data=$elements['adm_csrf_token']}

    <div id="plg_wc_template_choice" class="card admidio-field-group">
        <div class="card-header">{$l10n->get('PLG_WC_CHOOSE_TEMPLATE')}</div>
        <div class="card-body">
            {include 'sys-template-parts/form.select.tpl' data=$elements['plg_wc_template']}
        </div>
    </div>
    <div id="plg_wc_selection" class="card admidio-field-group">
        <div class="card-header">{$l10n->get('PLG_WC_SELECTION')}</div>
        <div class="card-body">
            {include 'sys-template-parts/form.checkbox.tpl' data=$elements['sender_user']}
            {include 'sys-template-parts/form.checkbox.tpl' data=$elements['recipient_mode']}
        </div>
    </div>
    <div id="plg_wc_sender_manual" class="card admidio-field-group">
        <div class="card-header">{$l10n->get('SYS_SENDER')}</div>
        <div class="card-body">
            {include 'sys-template-parts/form.input.tpl' data=$elements['plg_wc_sender_organization']}
            {include 'sys-template-parts/form.input.tpl' data=$elements['plg_wc_sender_name']}
            {include 'sys-template-parts/form.input.tpl' data=$elements['plg_wc_sender_street']}
            {include 'sys-template-parts/form.input.tpl' data=$elements['plg_wc_sender_postcode']}
            {include 'sys-template-parts/form.input.tpl' data=$elements['plg_wc_sender_city']}
        </div>
    </div>
    <div id="plg_wc_recipient_role" class="card admidio-field-group">
        <div class="card-header">{$l10n->get('SYS_ROLE')}</div>
        <div class="card-body">
            {include 'sys-template-parts/form.select.tpl' data=$elements['role_select']}
            {include 'sys-template-parts/form.select.tpl' data=$elements['show_members']}
        </div>
    </div>
    <div id="plg_wc_recipient_manual" class="card admidio-field-group">
        <div class="card-header">{$l10n->get('SYS_RECIPIENT')}</div>
        <div class="card-body">
            {include 'sys-template-parts/form.input.tpl' data=$elements['plg_wc_recipient_organization']}
            {include 'sys-template-parts/form.input.tpl' data=$elements['plg_wc_recipient_name']}
            {include 'sys-template-parts/form.input.tpl' data=$elements['plg_wc_recipient_street']}
            {include 'sys-template-parts/form.input.tpl' data=$elements['plg_wc_recipient_postcode']}
            {include 'sys-template-parts/form.input.tpl' data=$elements['plg_wc_recipient_city']}
        </div>
    </div>
    <div id="plg_wc_description" class="card admidio-field-group">
        <div class="card-header">{$l10n->get('SYS_TEXT')}</div>
        <div class="card-body">
            {include 'sys-template-parts/form.input.tpl' data=$elements['plg_wc_subject']}
            {include 'sys-template-parts/form.editor.tpl' data=$elements['plugin_CKEditor']}
        </div>
    </div>
    <div class="form-alert" style="display: none;">&nbsp;</div>
    {include 'sys-template-parts/form.button.tpl' data=$elements['adm_button_send']}
</form>
