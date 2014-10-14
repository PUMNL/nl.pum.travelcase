{*
 * Template file to display related travel cases
 *
 *}

<div id="case-travel-cases" class="crm-accordion-wrapper collapsed">

<div class="crm-accordion-header">{ts}Travel cases{/ts}</div>

<div class="crm-accordion-body">
{if $permission EQ 'edit' && $expert}
    {capture assign=newTravelCaseUrl}{crmURL p="civicrm/case/add" q="reset=1&action=add&cid=`$expert.id`&context=case"}{/capture}
    <div class="action-link">
        <a accesskey="N" href="{$newTravelCaseUrl}" class="button">
            <span><div class="icon add-icon"></div>{ts}New travel case for{/ts} {$expert.display_name}</span>
        </a>
    </div>

{/if}
<table>
    <thead>
        <tr>
            <th class="ui-state-default">{ts}Client{/ts}</th>
            <th class="ui-state-default">{ts}Status{/ts}</th>
            <th class="no-sort ui-state-default"></th>
        </tr>
     </thead>
     <tbody>
        
        {foreach from=$travel_cases item=case}
            <tr class="{cycle values="odd,even"}">
                <td>{$case.display_name}</td>
                <td>{$case.status}</td>
                <td>
                    <a href="{crmURL p="civicrm/contact/view/case" q="action=view&reset=1&id=`$case.case_id`&cid=`$case.client_id`&context=case"}">{ts}Manage case{/ts}
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
    var caseTravelCases = cj('#case-travel-cases').detach();
    cj('#view-related-cases').after(caseTravelCases);
});
{/literal}
</script>