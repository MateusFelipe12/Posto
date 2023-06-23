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

const setItemStorage = (item) => {
    if (!window[item]) window[item] = [];
    localStorage.setItem(item, JSON.stringify(window[item]));
    return window[item];
}

const getItemStorage = (item) => {
    window[item] = JSON.parse(localStorage.getItem(item));
    if (!window[item]) window[item] = [];
    return window[item];
}

const clearStorage = (item) => {
    delete window[item];
    localStorage.removeItem(item)
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
        console.log(this)
        let url = $(this).attr('href')
        goTo(url);
        return false
    })

    $('body').on('click', 'button:not([content_modal])', function(e){
        let url = $(this).attr('href')
        console.log(url)
        if(url){
            console.log(this)
            e.preventDefault();
            $.post(url+'&get=1', '', (e)=>{handleResponse(e)});
            return false
        } 
        // return true; // é de formulário
    })

    if( window.user && window.user.email.length ){
        $.get(window.location.pathname+'?get=1', '', (e)=>{handleResponse(e)});
    } else{
        $.get('/login?get=1', '', (e)=>{handleResponse(e)});
    }
}

function handleResponse(e) {
    console.log(e);
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
        console.log(url+'&get=1');

        $.get(url+'&get=1', '', function(e){
            let content = e.html;
            let id = window.counter ? ++window.counter : window.counter = 1;
            console.log(id)
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
    console.log(path)

    $('.modal').modal('hide');
    $('.collapse').collapse('hide');

    $.get(path+'?get=1','', function(e){handleResponse(e)});
    window.history.pushState({page: path}, '', path);

}

window.addEventListener('popstate', function (event) {
    goTo(event.state.page)
})


const onload = function () {
    forDoAjax();
    modal_component();

};


$(document).on('DOMContentLoaded', onload);
