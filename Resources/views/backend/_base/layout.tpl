<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{link file="backend/_resources/css/bootstrap.min.css"}">
    <link rel="stylesheet" href="{link file="backend/_resources/js/chosen/chosen.css"}">
</head>
<body role="document" style="padding-top: 0px">

<div class="container theme-showcase" role="main">
    {block name="content/main"}{/block}
</div> <!-- /container -->

<script type="text/javascript" src="{link file="backend/base/frame/postmessage-api.js"}"></script>
<script type="text/javascript" src="{link file="backend/_resources/js/jquery-2.1.4.min.js"}"></script>
<script type="text/javascript" src="{link file="backend/_resources/js/bootstrap.min.js"}"></script>
<script type="text/javascript" src="{link file="backend/_resources/js/chosen/chosen.jquery.js"}"></script>

{block name="content/layout/javascript"}
<script type="text/javascript">
    $(document).ready( function () {
        $('#select-prices-from').chosen( { disable_search_threshold: 10 } );
        $('#select-prices-to').chosen( { disable_search_threshold: 10 } );

        $("#select-prices-from").on("change", function (){
            let fromGroup = $(this).val();
            $("#select-prices-to option").attr("disabled", false);
            $("#select-prices-to option[value='"+fromGroup+"']").attr("disabled", true);
            $('#select-prices-to').trigger("chosen:updated");
        });

        $("#copyPrices").on("click", function (event) {
            event.preventDefault();
            var fromGroupSelect = $("#select-prices-from");
            var toGroupsSelect = $("#select-prices-to");

            fromGroupSelect.parent().parent().css("border-color", "#ddd");
            toGroupsSelect.parent().parent().css("border-color", "#ddd");
            if(fromGroupSelect.val().trim().length > 0 === false) {
                fromGroupSelect.parent().parent().css("border-color", "red");
                fromGroupSelect.trigger("chosen:updated");
                fromGroupSelect.focus();
                return false;
            }else if(!toGroupsSelect.val() || toGroupsSelect.val().length > 0 === false){
                toGroupsSelect.parent().parent().css("border-color", "red");
                toGroupsSelect.trigger("chosen:updated");
                toGroupsSelect.focus();
                return false;
            }else{
                if (window.confirm("Please check again! Do you really want to copy these prices?")) {
                    $("#copyPrices").attr("disabled", true).html("Please wait ...");
                    $.ajax({
                        type: "POST",
                        url: "{url controller='PriceCopyModulePlainHtml' action='copyPrices'}",
                        data: {
                            "fromGroup": fromGroupSelect.val(),
                            "toGroups": toGroupsSelect.val()
                        },
                        dataType: "json",
                        success: function (response) {
                            $("#copyPrices").attr("disabled", false).html("Copy prices");

                            var dt = new Date();
                            var time = "Execution time: " + dt.getDay() +"." + dt.getMonth() + "." + dt.getFullYear() + " " + dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds() + "<br><br>";
                            var html = time;

                            if(response.code == 400){
                                postMessageApi.createAlertMessage("Price copy Error", response.message);
                                html += "<strong>"+response.message+"</strong>";
                                $("#messages").html('<div class="alert alert-danger" role="alert">'+html+'</div>');
                            }else{
                                postMessageApi.createAlertMessage("PriceCopy", "Prices copied!");
                                html += "<strong>Deleting old prices:</strong>&nbsp;" + (response.message.pricesDelete ? "Done" : "Failed") + "<br>";
                                html += "<strong>Inserting new prices:</strong>&nbsp;" + (response.message.pricesInsert ? "Done" : "Failed") + "<br>";
                                if(response.message.pricesDelete && response.message.pricesInsert){
                                    html += "<br><strong>If there are active discount_per_square you need to save each one of them again!</strong>";
                                }
                                var alertType = response.message.pricesDelete && response.message.pricesInsert ? "alert-success" : "alert-warning";
                                $("#messages").html('<div class="alert '+alertType+'" role="alert">'+html+'</div>');
                            }
                        },
                        error: function(er){
                            //console.log(er);
                        }
                    });
                }
            }


        });

        $("#roundPrices").on("click", function (event) {
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "{url controller='PriceCopyModulePlainHtml' action='roundPrices'}",
                data: {
                },
                dataType: "json",
                success: function (response) {
                    alert("test")
                },
                error: function(er){
                    //console.log(er);
                }
            });

        });
    });
</script>
{/block}
{block name="content/javascript"}{/block}
</body>
</html>