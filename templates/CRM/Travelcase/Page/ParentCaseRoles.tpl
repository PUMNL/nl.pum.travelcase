{*
 * Template file to display roles of parent case
 *
 *}

<div id="case-parent-case-roles" class="crm-accordion-wrapper collapsed">

<div class="crm-accordion-header">{ts}Parent case roles{/ts}</div>

<div class="crm-accordion-body">
<table>
    <thead>
        <tr>
            <th class="ui-state-default">{ts}Case role{/ts}</th>
            <th class="ui-state-default">{ts}Name{/ts}</th>
            <th class="ui-state-default">{ts}Email{/ts}</th>
        </tr>
     </thead>
     <tbody>
        
        {foreach from=$relationships item=role}
            <tr class="{cycle values="odd,even"}">
                <td>{$role.relationship_type}</td>
                <td>
                    <a href="{$role.contact_link}">
                    {$role.contact_display_name}
                    </a>
                </td>
                <td>
                    {if ($role.email)}
                        <a href="{$role.email_link}" title="{ts}compose and send an email{/ts}">
                            <div class="icon email-icon" title="{ts}compose and send an email{/ts}"></div>
                        </a>
                    {/if}
                </td>
            </tr>
        {/foreach}
    </tbody>
</table>

</div>
</div>

<script type="text/javascript">
{literal}
cj(function() {
    var parentCaseRoles = cj('#case-parent-case-roles').detach();
    cj('.crm-case-roles-block').after(parentCaseRoles);
});
{/literal}
</script>