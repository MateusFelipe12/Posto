$.toast = (text, type='') => {
    let style = {};
    switch(type){
        case 'danger':
        case 'error':
            style.background = '#dc3545'
        break;
        case 'success':
            style.background = '#198754'
        break;
        case 'warning':
            style.background = '#ffc107'
        break;
        default:
            style.background = '#0dcaf0'
        break;
    }

    Toastify({
        text: text,
        duration: 2000,
        gravity: "bottom",
        position: "left",
        style
    }).showToast();
    return true;
}

$('#root').on('keyup', '[name="cnpj"]', function(){
    $(this).val(formatValue($(this).val(), 'cnpj'));
})

$('#root').on('keyup', '[name="state_registration"]', function(){
    $(this).val(formatValue($(this).val(), 'state_registration'));
})

// $('#root').on('keyup', '[placeholder="Contato"]', function(){
//     $(this).val(formatValue($(this).val(), 'telefone'));
// })

const formatValue = function(value, type) {
    switch (type) {
        case 'cpf':
            return value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4'); // Formata CPF
        break;
        case 'telefone':
            value = value.replace(/\D/g, '').slice(0,11)
            return value.replace(/(\d{2})(\d{1})(\d{4})(\d{4})/, '($1) $2 $3-$4'); // Formata CNPJ
        break;
        case 'cnpj':
            value = value.replace(/\D/g, '').slice(0,14)
            return value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5'); // Formata CNPJ
        break;
        case 'state_registration':
            value = value.replace(/\D/g, '').slice(0,9)
            return value.replace(/(\d{2})(\d{3})(\d{3})(\d{1})/, '$1.$2.$3-$4'); // Formata CNPJ
        break;
    }
    return value;
}

const forDoAjax = () => {
    $('body').on('submit', 'form', function(e){
        e.preventDefault();
        let url = $(this).attr('action');
        let method = $(this).attr('method');
        let data = $(this).serialize();
        switch(method.toLowerCase()){
            case 'post':
                $.post(url+'&get=1', data, (e)=>{handleResponse(e)});
            break
                case 'get':
                $.get(url+'&get=1', data, (e)=>{handleResponse(e)});
            break
        }
        return false
    })

    $('body').on('click', 'a', function(e){
        e.preventDefault();
        let url = $(this).attr('href')
        goTo(url);
        return false
    })

    $('body').on('click', 'button:not([content_modal])[href]', function(e){
        let url = $(this).attr('href')
        if(url){
            e.preventDefault();
            $.post(url+'&get=1', '', (e)=>{handleResponse(e)});
            return false
        }
        return true; // é de formulário
    })

    if( window.user && window.user.email.length ){
        $.get(window.location.pathname+'?get=1', '', (e)=>{handleResponse(e)});
    } else{
        $.get('/login?get=1', '', (e)=>{handleResponse(e)});
    }
}

function handleResponse(e) {
    Object.keys(e).forEach(content => {
        switch (content) {
            case 'js':
                eval(e[content]);
                break;
            case 'html':
                $('html body').append(e[content]);
                break;
            case 'page':
            default:
                if (e[content].length > 1) {
                    $('html body #root').html(e[content]);
                } else {
                    $('html body #root').html(e);
                }
                break;
        }
    });

}

const modal_component = () => {

    $('body').on('click', 'button[content_modal]:not(.ok)', function(e){
        let btn = $(this)
        let url = btn.attr('content_modal')
        if(!url) return true;

        $.get(url+'&get=1', '', function(e){
            let content = e.html;
            let id = window.counter ? ++window.counter : window.counter = 1;
            let modal = `
                <div class="modal fade" id="modal-${id}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body">${content}</div>
                        </div>
                    </div>
                </div>`
            
                $('body #root').append(modal)
                btn.attr('data-bs-target', '#modal-'+id)
                btn.attr('data-bs-toggle', 'modal');
                $('#modal-'+id).modal('show')
                btn.addClass('ok');
        })

    })
}

const goTo = function(path='/'){

    $('.modal').modal('hide');
    $('.collapse').collapse('hide');

    $.get(path+'?get=1','', function(e){handleResponse(e)});
    window.history.pushState({page: path}, '', path);

}

window.addEventListener('popstate', function (event) {
    goTo(event.state.page)
})

const addForms = function(){
    let html_add_contact = '';
    $('body').on('click', '#add-new-contact-provider', function(){
        if(!html_add_contact.length){
            $(this).closest('form').find('.form-contacts').each(function(){
                let classes = $(this).attr('class');
                html_add_contact += `<div class="${classes}">${
                    $(this).html()
                }</div>`;
            })
        }
        $(this).closest('form').find('.row').append(html_add_contact);
    })
    
    let html_add_products = '';
    $('body').on('click', '#add-new-product-supply', function(){
        if(!html_add_products.length){
            $(this).closest('form').find('.form-products-suplly').each(function(){
                let classes = $(this).attr('class');
                html_add_products += `<div class="${classes}">${
                    $(this).html()
                }</div>`;
            })
        }
        $(this).closest('form').find('>.row').append(html_add_products);
    })
}

const onload = function () {
    forDoAjax();
    modal_component();
    addForms();
};


$(document).on('DOMContentLoaded', onload);
