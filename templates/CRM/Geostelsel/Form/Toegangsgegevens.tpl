<h3>{if $action eq 1}{ts}Nieuwe toegangsgegevens{/ts}{elseif $action eq 2}{ts}Bewerk toegangsgegevens{/ts}{else}{ts}Verwijder toegangsgegevens{/ts}{/if}</h3>

<div class="crm-block crm-form-block crm-toegangsgegevens-form-block">
    {if $action eq 8}
        <div class="">
            <div class="icon inform-icon"></div>
            {ts}Deleting toegangsgegevens cannot be undone.{/ts} {ts}Do you want to continue?{/ts}
        </div>
        <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
    {else}
        <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>

        <div class="crm-section">
            <div class="label">{$form.link.label}</div>
            <div class="content">{$form.link.html}</div>
            <div class="clear"></div>
        </div>

        <div class="crm-section">
            <div class="label">{$form.type.label}</div>
            <div class="content">{$form.type.html}</div>
            <div class="clear"></div>
        </div>

        <div class="crm-section type-AfdelingMember type-option">
            <div class="label">{$form.toegang_tot_contacten_van.label}</div>
            <div class="content">{$form.toegang_tot_contacten_van.html}</div>
            <div class="clear"></div>
        </div>

        <div class="crm-section type-option type-GroupMember">
            <div class="label">{$form.group_id.label}</div>
            <div class="content">{$form.group_id.html}</div>
            <div class="clear"></div>
        </div>

        <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
    {/if}
</div>

<script type="text/javascript">
    {literal}
    cj(function() {
        cj('select#type').change(changeType);
        cj('select#type').trigger('change');
    });

    function changeType() {
        cj('.type-option').css('display', 'none');
        var val = cj('#type').val();
        cj('.type-'+val).css('display', 'block');
    }
    {/literal}
</script>