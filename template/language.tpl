{include file="messages.tpl"}


{if $TALK_URI}<a href="{$TALK_URI}" class="floating_link">Talk</a>{/if}

<p class="legend">
  <a href="{$lex->language_url($LANGUAGE)}">{$lex->language_flag($LANGUAGE)} {$lex->language_name($LANGUAGE)}</a>
</p>

<ul id="projects" class="list-cloud {if $USE_PROJECT_STATS}w-stats{/if}">
{assign var=category value=0}
{foreach from=$projects item=row}{strip}
  {if $USE_PROJECT_CATS}
    {if not empty($row.CATEGORY_ID) and $category!=$row.CATEGORY_ID}
      {assign var=category value=$row.CATEGORY_ID}
      <h3>{$row.CATEGORY_NAME}</h3>
    {elseif empty($row.CATEGORY_ID) and $category!=0}
      {assign var=category value=0}
      <h3>Other</h3>
    {/if}
  {/if}
  
  {/strip}<li>
    <a href="{$row.URL}">{strip}
      {$row.NAME} {$row.FLAG}
      {$row.PROGRESS_BAR}
    {/strip}</a>
  </li>
{/foreach}

{if $PROJECT_NOT_TRANSLATED}
  <li class="add">
    <b>{$PROJECT_NOT_TRANSLATED} project(s) not translated</b> <a href="#"><img src="template/images/bullet_add.png" alt="+"> Translate another project</a>
  </li>
{/if}
</ul>

{if $PROJECT_NOT_TRANSLATED}
<div id="dialog-form" title="Translate another project" style="display:none;">
  {ui_message type="highlight" icon="info" content="You can only add projects you have permission to translate."}
  <form action="{$SELF_URL}" method="post" style="text-align:center;">
    Select a project : 
    <select name="project">
      <option value="-1">----------</option>
      {html_options options=$project_available}
    </select>
    <input type="hidden" name="add_project" value="1">
  </form>
</div>
{/if}

{if $PROGRESS_BAR}
  {ui_message type="highlight" icon="signal" content="<b>Language progression :</b> "|cat:$PROGRESS_BAR}
{/if}

{if $users}
  {foreach from=$users item=row}
    {append var=content value='<a href="'|cat:$row.url|cat:'" style="color:#'|cat:$row.color|cat:';">'|cat:$row.username|cat:'</a>'}
  {/foreach}
  {assign var=content value=', '|implode:$content}
  {ui_message type="highlight" icon="person" content='<b>Users :</b> '|cat:$content}
{/if}


{footer_script}{literal}
$("#dialog-form").dialog({
  autoOpen: false, modal: true, resizable: false,
  height: 200, width: 440,
  show: "clip", hide: "clip",
  buttons: {
    "Add": function() { $("#dialog-form form").submit(); },
    "Cancel": function() { $(this).dialog("close"); }
  }
});

$(".add a").click(function() {
  $("#dialog-form").dialog("open");
  return false;
});
{/literal}{/footer_script}

{if $MAKE_STATS_KEY}
  {footer_script}{literal}
  $.ajax({
    type: "POST",
    url: "ajax.php",
    data: {{/literal} "action":"make_stats", "language":"{$LANGUAGE}", "key":"{$MAKE_STATS_KEY}" {literal}}
  }).done(function(msg) {
    msg = $.parseJSON(msg);
    if (msg.errcode == "success") {
      //document.location.reload(true);
    }
  });
  {/literal}{/footer_script}
{/if}