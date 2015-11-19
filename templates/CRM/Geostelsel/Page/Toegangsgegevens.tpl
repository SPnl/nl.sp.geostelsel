{if $action eq 1 or $action eq 2 or $action eq 8}
    {include file="CRM/Geostelsel/Form/Toegangsgegevens.tpl"}
{else}
    <div id="ltype">
        <div class="form-item">
            {strip}
                <table cellpadding="0" cellspacing="0" border="0">
                    <thead class="sticky">
                    <th>{ts}Toegang tot{/ts}</th>
                    <th></th>
                    </thead>
                    {foreach from=$rows item=row name=top_level_access}
                        <tr id="row_{$row.id}"class="{cycle values="odd-row,even-row"} {$row.class}">
                            <td>
                                {if ($smarty.foreach.top_level_access.first)}
                                    Contacten die
                                {else}
                                    {$row.link_label}
                                {/if}
                                &nbsp;
                                {$row.type_label}
                                &nbsp;
                                {if ($row.type == 'AfdelingMember')}
                                    '{$row.toegang_tot_contacten_van_label}'
                                {else}
                                    '{$row.group_id_label}'
                                {/if}
                                &nbsp;zijn
                            <td>{$row.action}</td>
                        </tr>
                    {/foreach}
                </table>
            {/strip}
        </div>

        {if $action ne 1 and $action ne 2}
            <div class="action-link">
                <a href="{crmURL q="action=add&reset=1&cid=`$cid`"}" id="newToegangsgegevens" class="button"><span><div class="icon add-icon"></div>{ts}Add toegangsgegevens{/ts}</span></a>
            </div>
        {/if}

    </div>
{/if}