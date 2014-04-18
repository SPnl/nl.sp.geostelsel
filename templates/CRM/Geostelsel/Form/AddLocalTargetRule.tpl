{* HEADER *}

<div class="crm-block crm-form-block">

<table class="form-layout">
    <tr>
        <td class="label">{ts}Contact{/ts}</td>
        <td>{$contact.display_name}</td>
    </tr>

    {foreach from=$elementNames item=elementName}
        <tr>
            <td class="label">{$form.$elementName.label}</td>
            <td class="content">{$form.$elementName.html}</td>
        </tr>
    {/foreach}

</table>

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

<script type="text/javascript">
{literal}
cj(function() {
    var gemeenteUrl = "/civicrm/ajax/postcodenl/autocomplete?reset=1&field=gemeente";

    cj('#gemeente').autocomplete( gemeenteUrl, { 
        width : 280, 
        selectFirst : true, 
        matchContains: true
    });

});
{/literal}
</script>

</div>