{*
 * Template file to display related travel cases
 *
 *}
{if $linked_to_case}
<script type="text/javascript">
{literal}
cj(function() {
    {/literal}
    var block_id = '#{$block_id} table.crm-info-panel tr:first td.html-adjust';
    var linkedToCase = '<a href="{$linked_to_case.url}">{$linked_to_case.label}</a>';
    {literal}
    cj(block_id).html(linkedToCase);
});
{/literal}
</script>
{/if}

{if $linked_to_event}
<script type="text/javascript">
{literal}
cj(function() {
    {/literal}
    var block_id = '#{$block_id} table.crm-info-panel tr:last td.html-adjust';
    var linkedToEvent = '<a href="{$linked_to_event.url}">{$linked_to_event.label}</a>';
    {literal}
    cj(block_id).html(linkedToEvent);
});
{/literal}
</script>
{/if}