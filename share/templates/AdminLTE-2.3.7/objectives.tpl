{extends file="base.tpl"}

{block name=title}{$page_title}{/block}
{block name=description}{$smarty.block.parent}{/block}
{block name=nav}{$smarty.block.parent}{/block}

{block name=additional_scripts}{$smarty.block.parent}
{if isset($userPaginator)} 
    {literal}
    <script type="text/javascript" > 
        $(document).ready(
                resizeBlocks('row_objectives_userlist', ['coursebook'])
        );
        $(document).ready(function () {
            small       = false;
            if ($('#f_userlist').hasClass('active')){
                floating_table('body-wrapper', 'userPaginator', ['username', 'role_name', 'completed', 'online'], 'menu_top_placeholder', 'container_userPaginator', 'default_userPaginator_position');
            }
        });
    </script>
    
    {/literal}
{/if} 
{/block}
{block name=additional_stylesheets}{$smarty.block.parent}{/block}

{block name=content} 
<!-- Content Header (Page header) -->
{content_header p_title=$page_title pages=$breadcrumb help='https://curriculumonline.gitbook.io/documentation/benutzerhandbuch/lernstand'}   

<!-- Main content, id section_content used to reload content with ajax-->
<section id="section_content" class="content">
    <div id="row_content" class="row">
        <div class="col-sm-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li {if isset($f_userlist)}class="active"{/if}><a href="#f_userlist" data-toggle="tab" onclick='processor("config","page", "config",{["tab"=>"f_userlist"]|@json_encode nofilter});'>Kursliste</a></li>
                    {if isset($coursebook) AND checkCapabilities('menu:readCourseBook', $my_role_id, false)}
                        <li {if isset($f_coursebook)}class="active"{/if}><a href="#f_coursebook" data-toggle="tab" onclick='processor("config","page", "config",{["tab"=>"f_coursebook"]|@json_encode nofilter});'>Kursbuch</a></li>
                    {/if}
                </ul>
                <div class="tab-content">
                    <div class="tab-pane {if isset($f_userlist)}active{/if}" id="f_userlist">
                        {if isset($courses)}
                            <form method='post' action='index.php?action=objectives&course={$selected_curriculum_id}{*&userID={implode(',',$selected_user_id)}&next={$currentUrlId}*}'>        
                                <div class="form-horizontal">
                                    <div class="row">
                                        <div class="col-md-4 col-sm-12">
                                            {Form::input_select('course', '', $courses, 'group, curriculum', 'id', $selected_curriculum_id, null, "window.location.assign('index.php?action=objectives&course='+this.value);", 'Kurs / Klasse wählen...', '', 'col-sm-12')}
                                        </div>
                                        {*Zertifikat*}
                                        <div class="col-md-2 col-sm-12">
                                            <div class='btn btn-default' onclick="formloader('generate_certificate','',{$sel_curriculum});">
                                                <span class="fa fa-files-o" aria-hidden="true"></span> {if count($selected_user_id) > 1} Zertifikate/Gruppen-Übersicht {else} Zertifikat/Gruppen-Übersicht {/if}erstellen
                                            </div>
                                        </div>
                                        <input id="certificate_template" class="hidden" value="false"/>{* hack to get js working if no user is selected, todo: remve certificate_template in js not used any more *}
                                    </div>
                                </div>
                            </form>
                        {else}<strong>Sie haben noch keine Lehrpläne angelegt bzw. noch keine Klassen eingeschrieben.</strong>
                        {/if}
                        {if isset($userPaginator)}   
                            <p> Bitte  Schüler aus der Liste auswählen um den Lernstand einzugeben.</p>
                            <div id="default_userPaginator_position" >
                                    {html_paginator id='userPaginator' title='Kurs'} 
                            </div>
                        {elseif $showuser eq true}Keine eingeschriebenen Benutzer{/if}
                    </div>
                    {if isset($coursebook)} 
                    <div class="tab-pane {if isset($f_coursebook)}active{/if}" id="f_coursebook">
                        {Render::courseBook($coursebook)}
                    </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
    
    {*if isset($userPaginator)*}
    
    <div>
    <div id="curriculum_content" class="row ">
    {if $show_course != '' and isset($terminalObjectives)}    
        <div class="col-xs-12">
            <div class="box box-default">
                <div class="box-header ">
                    {if isset($user->avatar)}
                        {*if $user->avatar_id neq 0*}
                        {Render::split_button($cur_content)}
                        <img src="{$access_file}{$user->avatar}" style="height:40px;"class="user-image pull-left margin-r-5" alt="User Image">
                        {*/if*}
                    {/if}
                    {Render::badge_preview(["reference_id" => $sel_curriculum, "user_id" => $selected_user_id])}

                    <p class="pull-right">Farb-Legende:
                    <button class="btn btn-success btn-flat" style="cursor:default">selbständig erreicht</button>
                    <button class="btn btn-warning btn-flat" style="cursor:default">mit Hilfe erreicht</button>
                    <button class="btn btn-danger btn-flat" style="cursor:default">nicht erreicht</button>
                    <button class="btn btn-default disabled btn-flat" style="cursor:default">nicht bearbeitet</button>
                    </p>
                </div>
                <div class="box-body" style="min-height:400px;">
        
                {if $show_course != '' and isset($terminalObjectives)} 
                    {foreach key=terid item=ter from=$terminalObjectives}
                        <div class="row" >
                            <div class="col-xs-12"> 
                                {*Thema Row*}
                                {RENDER::objective(["type" =>"terminal_objective", "objective" => $ter , "user_id" => $selected_user_id,"group_id" => $sel_group_id])}
                                {*Ende Thema*}

                                {*Anfang Ziel*}
                                {foreach key=enaid item=ena from=$enabledObjectives}
                                    {if $ena->terminal_objective_id eq $ter->id}
                                        {RENDER::objective(["type" =>"enabling_objective", "objective" => $ena , "user_id" => $selected_user_id, "group_id" => $sel_group_id, "border_color" => $ter->color])}
                                    {/if}
                                {/foreach}
                                {*Ende Ziel*}
                            </div>
                        </div>
                    {/foreach}		
                {else}
                    {if isset($selected_user_id) and $show_course != ''}
                        <p>Es wurden noch keine Lernziele eingegeben.</p>
                        <p>Dies können sie unter Lehrpläne --> Lernziele/Kompetenzen hinzufügen machen.</p>
                    {else} 
                        {if isset($curriculum_id)}<!--Wenn noch keine Lehrpläne angelegt wurden-->
                        <p>Bitte wählen sie einen Benutzer aus.</p>
                        {/if}            
                    {/if}
                {/if} 
                </div>
            </div>
        </div>
    {/if}
    </div>
    </div>
    
</section>
{/block}

{block name=sidebar}{$smarty.block.parent}{/block}
{block name=footer}{$smarty.block.parent}{/block}
