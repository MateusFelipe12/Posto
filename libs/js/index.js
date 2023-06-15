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
    console.log(type, style)

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
        let url = $(this).attr('href')
        goTo(url);
        return false
    })

    $.get('/login?get=1', '', (e)=>{handleResponse(e)});
}

const handleResponse = function (e){
    console.log(e);
    Object.keys(e).forEach(content => {
        switch (content) {
            case 'js':
                eval(e[content])
            break;
            case 'html': 
                $('html body').append(e[content]);
            break;
            case 'page': 
            default:
                if(e[content].length > 1){
                    $('html body #root').html(e[content]);
                } else{
                    $('html body #root').html(e);
                }
            break;
        }
    });

}

const goTo = function(path='/'){
    $.get(path+'?get=1','', function(e){handleResponse(e)});
    window.history.pushState({page: path}, '', path);
}

window.addEventListener('popstate', function (event) {
    goTo(event.state.page)
})


const onload = function () {
    forDoAjax();



};


$(document).on('DOMContentLoaded', onload);
