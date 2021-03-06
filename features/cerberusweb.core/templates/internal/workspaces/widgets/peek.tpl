{$peek_context = CerberusContexts::CONTEXT_WORKSPACE_WIDGET}
<form action="{devblocks_url}{/devblocks_url}" method="post" id="frmWidgetEdit">
<input type="hidden" name="c" value="internal">
<input type="hidden" name="a" value="handleSectionAction">
<input type="hidden" name="section" value="dashboards">
<input type="hidden" name="action" value="saveWidgetPopup">
{if !empty($widget) && !empty($widget->id)}<input type="hidden" name="id" value="{$widget->id}">{/if}
<input type="hidden" name="do_delete" value="0">
<input type="hidden" name="_csrf_token" value="{$session.csrf_token}">

<table width="100%" cellpadding="0" cellspacing="2">
	{if $extension instanceof Extension_WorkspaceWidget}
	<tr>
		<td width="1%" nowrap="nowrap" valign="top">
			<b>Type:</b>
		</td>
		<td width="99%" valign="top">
			{$extension->manifest->name} 
		</td>
	</tr>
	{/if}
	<tr>
		<td width="1%" nowrap="nowrap">
			<b>Label:</b>
		</td>
		<td width="99%">
			<input type="text" name="label" value="{$widget->label}" size="45">
		</td>
	</tr>
	<tr>
		<td width="1%" nowrap="nowrap">
			<b>Cache:</b>
		</td>
		<td width="99%">
			<input type="text" name="cache_ttl" value="{$widget->cache_ttl}" size="10"> seconds
		</td>
	</tr>
</table>

{* The rest of config comes from the widget *}
{if $extension instanceof Extension_WorkspaceWidget}
{$extension->renderConfig($widget)}
{/if}

{if $active_worker->hasPriv("contexts.{$peek_context}.delete")}
<fieldset class="delete" style="display:none;">
	<legend>Are you sure you want to delete this widget?</legend>
	<button type="button" class="red delete">{'common.yes'|devblocks_translate|capitalize}</button>
	<button type="button" onclick="$(this).closest('fieldset').fadeOut().siblings('div.toolbar').fadeIn();">{'common.no'|devblocks_translate|capitalize}</button>
</fieldset>
{/if}

<div style="margin-top:10px;" class="toolbar">
	{if (!$widget->id && $active_worker->hasPriv("contexts.{CerberusContexts::CONTEXT_WORKSPACE_WIDGET}.create")) || ($widget->id && $active_worker->hasPriv("contexts.{CerberusContexts::CONTEXT_WORKSPACE_WIDGET}.update"))}<button type="button" class="submit"><span class="glyphicons glyphicons-circle-ok" style="color:rgb(0,180,0);"></span> {'common.save_changes'|devblocks_translate|capitalize}</button>{/if}
	{if !empty($widget->id) && $active_worker->hasPriv("contexts.{$peek_context}.delete")}<button type="button" onclick="$(this).closest('div.toolbar').fadeOut().siblings('fieldset.delete').fadeIn();"><span class="glyphicons glyphicons-circle-remove" style="color:rgb(200,0,0);"></span> {'common.delete'|devblocks_translate|capitalize}</button>{/if}
</div>

</form>

<script type="text/javascript">
$(function() {
	var $popup = genericAjaxPopupFind('#frmWidgetEdit');
	
	$popup.one('popup_open', function(event,ui) {
		$popup.dialog('option','title',"{'Widget'|escape:'javascript' nofilter}");
		$popup.css('overflow', 'inherit');
		
		var $frm = $popup.find('form');
		
		$frm.find('button.delete').click(function(e) {
			$frm.find('input:hidden[name=do_delete]').val('1');
			
			genericAjaxPost('frmWidgetEdit','',null,function(out) {
				var widget_id = $frm.find('input:hidden[name=id]').val();
	
				// Nuke the widget DOM
				$('#widget' + widget_id).remove();
				
				// Close the popup
				$popup.dialog('close');
			});
		});
		
		$frm.find('button.submit').click(function(e) {
			genericAjaxPost('frmWidgetEdit','',null,function(out) {
				var widget_id = $frm.find('input:hidden[name=id]').val();
				// Reload the widget
				genericAjaxGet('widget' + widget_id,'c=internal&a=handleSectionAction&section=dashboards&action=renderWidget&widget_id=' + widget_id + '&nocache=1');
				// Close the popup
				$popup.dialog('close');
			});
		});
	});
});
</script>
