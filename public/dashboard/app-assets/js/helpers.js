
function sweetConfirm(  callback) {
    Swal.fire({
        title: " تنبيه  !",
         text:"هل تريد إتمام العملية؟",
        type: "warning",
        showCancelButton: !0,
        confirmButtonColor: "#2F8BE6",
        cancelButtonColor: "#F55252",
        confirmButtonText: "تأكيد",
        confirmButtonClass: "btn btn-primary",
        cancelButtonClass: "btn btn-danger ml-1",
        cancelButtonText: "تراجع ",
        buttonsStyling: !1
    }) .then((confirmed) => {
        callback(confirmed && confirmed.value == true);
    });
}

 function showSuccesFunction(){
    Swal.fire({
        type: "success",
        title:  "تمت العملية بنجاح",

        confirmButtonClass: "btn btn-success"
    });}


    function showErrorFunction(){
        Swal.fire({
            type: "error",
            title:  "حدث خطأ ما",

            confirmButtonClass: "btn btn-primary"
        });
}


function showErrorFunctionMsg(msg){
    Swal.fire({
        type: "error",
        title: msg,

        confirmButtonClass: "btn btn-primary"
    });
}

function showErrorFunctionMsg(msg){
    Swal.fire({
        type: "error",
        title: msg,

        confirmButtonClass: "btn btn-primary"
    });
}










