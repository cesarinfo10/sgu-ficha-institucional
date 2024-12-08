
setTimeout(() => {
    llamarInfoConsolidada();
}, 3000);

function llamarInfoConsolidada(){
    
    var url ="models/query_ficha_institucional.php?getCarrera";
    $.ajax({
        type: "POST",
        url: url,
        async: false,
        success: function(data) {
            // console.log(data);
            $("#numCarrerasGrado").html(data);

        }

    });
}