$(document).ready(function(){

    function search(){

        var title=$("#search").val();

        if(title!=""){
            $("#result").html("<img alt='ajax search' src='../library/ajax-loader.gif'/>");
            $.ajax({
                type:"post",
                url:"search.php",
                data:"title="+title,
                success:function(data){
                    $("#result").html(data);
                    $("#search").val("");
                }
            });
        }



    }

    $("#button").click(function(){
        search();
    });

    $('#search').keyup(function(e) {
        if(e.keyCode == 13) {
            search();
        }
    });
});