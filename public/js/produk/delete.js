function deleteProduk(id,nama){

    $('#deleteTargetName').text(nama);

    $('#formDeleteProduk').attr(
        'action',
        `${window.produkConfig.deleteUrl}/${id}`
    );

    $('#modalDeleteGlobal').modal('show');

}


function reloadTable(){
    initClientTable();
}
