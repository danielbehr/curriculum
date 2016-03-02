{extends file="base.tpl"}

{block name=title}{$page_title}{/block}
{block name=description}{$smarty.block.parent}{/block}
{block name=nav}{$smarty.block.parent}{/block}

{block name=additional_scripts}{$smarty.block.parent}{/block}
{block name=additional_stylesheets}{$smarty.block.parent}{/block}

{block name=content}
    <h3 class="page-header">{$page_title}<input class="curriculumdocsbtn pull-right" type="button" name="help" onclick="curriculumdocs('http://docs.joachimdieterich.de/index.php?title=Backup');"/></h3>
        {$backup_form}

        {if isset($zipURL)}
            <p>Folgende Backups können heruntergeladen werden.</p>
            <p><a class="url_btn floatleft" href={$zipURL} ></a></p>
            <p>Aktuelle Sicherungsdatei herunterladen.</p></br></br>
        {/if} 
        
        {html_paginator id='fileBackupPaginator'}
{/block}

{block name=sidebar}{$smarty.block.parent}{/block}
{block name=footer}{$smarty.block.parent}{/block}