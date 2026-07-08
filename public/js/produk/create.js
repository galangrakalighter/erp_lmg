function initSelect2(){

    $('.select2-multiple').select2({

        theme:'bootstrap4',

        width:'100%',

        placeholder:function(){

            return $(this).data('placeholder');

        }

    });

}

function initHierarchy(){

    $('#select-buh').change(function(){

        let ids=$(this).val();

        if(ids && ids.length){

            $('#select-dm').prop('disabled',false);

            $('#select-ads').prop('disabled',false);

            $('#select-ccm').prop('disabled',false);

            filterOptions('select-dm',ids);

            filterOptions('select-ads',ids);

            filterOptions('select-ccm',ids);

        }else{

            resetSelect([
                'select-dm',
                'select-ads',
                'select-ccm',
                'select-cc'
            ]);

        }

    });

    $('#select-ccm').change(function(){

        let ids=$(this).val();

        if(ids && ids.length){

            $('#select-cc').prop('disabled',false);

            filterOptions('select-cc',ids);

        }else{

            resetSelect(['select-cc']);

        }

    });

}

function initPlatform(){

    $(document)

    .off('click','.add-platform')

    .on('click','.add-platform',function(){

        $('#platform-container').append(`
            <div class="platform-row mb-3">

                <div class="input-group">

                    <select name="platform[]" class="form-control col-md-4">

                        <option value="Instagram">Instagram</option>
                        <option value="Facebook">Facebook</option>
                        <option value="Tiktok">Tiktok</option>

                    </select>

                    <input
                        type="text"
                        name="access[]"
                        class="form-control"
                        placeholder="Input Access Token">

                    <div class="input-group-append">

                        <button
                            type="button"
                            class="btn btn-danger remove-platform">

                            -

                        </button>

                    </div>

                </div>

            </div>
        `);

    });

    $(document)

    .off('click','.remove-platform')

    .on('click','.remove-platform',function(){

        $(this)
            .closest('.platform-row')
            .remove();

    });

}

function filterOptions(childId,parentIds){

    let select=$('#'+childId);

    select.find('option').each(function(){

        let parent=$(this).data('parent');

        if(!parent){

            return;

        }

        if(parentIds.includes(parent.toString())){

            $(this).prop('disabled',false);

        }else{

            $(this)

                .prop('selected',false)

                .prop('disabled',true);

        }

    });

}

function resetSelect(ids){

    ids.forEach(function(id){

        $('#'+id)
            .val(null)
            .trigger('change')
            .prop('disabled',true);

    });

}
