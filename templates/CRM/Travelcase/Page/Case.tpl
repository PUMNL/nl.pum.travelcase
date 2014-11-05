{*
 * Template file to display related travel cases
 *
 *}

<div id="case-travel-cases" class="crm-accordion-wrapper collapsed">

<div class="crm-accordion-header">{ts}Travel cases{/ts}</div>

<div class="crm-accordion-body">
{if $permission EQ 'edit' && count($related_contacts)}
<div class="action-link">
    {foreach from=$related_contacts item=related_contact}
        {capture assign=newTravelCaseUrl}{crmURL p="civicrm/case/add" q="reset=1&action=add&cid=`$related_contact.id`&context=case&parent_case_id=`$caseId`"}{/capture}
        
            <a accesskey="N" href="{$newTravelCaseUrl}" class="button">
                <span><div class="icon add-icon"></div>{ts}New travel case for{/ts} {$related_contact.display_name}</span>
            </a>
    {/foreach}
        </div>
{/if}
<table>
    <thead>
        <tr>
            <th class="ui-state-default">{ts}Client{/ts}</th>
            <th class="ui-state-default">{ts}Status{/ts}</th>
            <th class="ui-state-default">{ts}Destination{/ts}</th>
            <th class="ui-state-default">{ts}Departure date{/ts}</th>
            <th class="ui-state-default">{ts}Return date{/ts}</th>
            <th class="no-sort ui-state-default"></th>
        </tr>
     </thead>
     <tbody>
        
        {foreach from=$travel_cases item=case}
            <tr class="{cycle values="odd,even"}">
                <td>{$case.display_name}</td>
                <td>{$case.status}</td>
                <td>{$case.destination}</td>
                <td>{$case.departure_date}</td>
                <td>{$case.return_date}</td>
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