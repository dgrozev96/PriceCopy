{extends file="parent:backend/_base/layout.tpl"}

{block name="content/main"}
    <style>
        .ordersList th{
            text-align: left;
        }
    </style>
    <div class="page-header">
        <h1>Customer group prices copier</h1>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">Prices from</div>
                <div class="panel-body">
                    <select data-placeholder="Choose a Country..." id="select-prices-from" style="display: block; width: 100%;" required>
                        <option value=""></option>
                        {section name=groupsFrom loop=$groups}
                        <option value="{$groups[groupsFrom].key}">{$groups[groupsFrom].name} ({$groups[groupsFrom].key})</option>
                        {/section}
                    </select>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">Prices to</div>
                <div class="panel-body">
                    <select data-placeholder="Choose a Country..." id="select-prices-to" multiple="" style="display: block; width: 100%;" required>
                        <option value=""></option>
                        {section name=groupsTo loop=$groups}
                            <option value="{$groups[groupsTo].key}">{$groups[groupsTo].name} ({$groups[groupsTo].key})</option>
                        {/section}
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div id="messages"></div>

    <div class="row">
        <div class="col-sm-12">
            <button id="copyPrices" type="button" class="btn btn-primary" style="width: 100%;">Copy prices</button>
        </div>
    </div>
    <div class="page-header">
        <h1>Prices round to .9</h1>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">Prices round</div>
                <div class="panel-body">
                    <button id="roundPrices" type="button" class="btn btn-primary" style="width: 100%;">Round prices</button>
                </div>
            </div>
        </div>
    </div>

    {*    <div><pre>{$orders|@var_dump}</pre></div>*}
{/block}