
function CMA_Disclaimer_CreateCookie(name, value, days){
    var date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    var expires = "; expires=" + date.toGMTString();
    document.cookie = name + "=" + value + expires + "; path=/";
}

function CMA_Disclaimer_ReadCookie(name){
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++){
        var c = ca[i];
        while(c.charAt(0) == ' ')
            c = c.substring(1, c.length);
        if(c.indexOf(nameEQ) == 0)
            return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function CMA_Disclaimer_CheckCookies(){
    if(CMA_Disclaimer_ReadCookie('cma_disclaimer') != 'Y')
    {
        var message = cma_disclaimer_opts.content;
        var message_container = document.createElement('div');
        message_container.id = 'disclaimer-message-container';
        message_container.setAttribute('style', 'z-index:998;position:fixed;width:100%;height:100%;top:0;left:0;background-color:rgba(0,0,0,0.5)');
        var html_code = '<div id="disclaimer-message" style="width:100%;max-width:800px;margin:0 auto;position:relative;top:40%;font-size: 11px; font-family: Arial,Helvetica,Sans-Serif; line-height: 20px; border-bottom: 1px solid rgb(211, 208, 208); text-align: left; background-color: #efefef; z-index: 999;"><div style="padding:20px">' + message;
        html_code += '<br /><br /><a href="javascript:CMA_Disclaimer_CloseCookiesWindow();" id="accept-disclaimer-checkbox" name="accept-disclaimer" style="background-color: #999; padding: 5px 10px; color: #FFF; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px; display: inline-block; margin-left: 0px; text-decoration: none; cursor: pointer;">' + cma_disclaimer_opts.acceptText + '</a>';
        html_code += '<a href="javascript:CMA_Disclaimer_RejectCookiesWindow();" id="reject-disclaimer-checkbox" name="reject-disclaimer" style="background-color: #999; margin-left: 10px; padding: 5px 10px; color: #FFF; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px; display: inline-block; text-decoration: none; cursor: pointer;">' + cma_disclaimer_opts.rejectText + '</a></div></div>';
        message_container.innerHTML = html_code;
        document.body.appendChild(message_container);
        var elem = document.getElementById('disclaimer-message');
        elem.style.marginTop = '-' + (elem.offsetHeight / 2) + 'px';
    }
}

function CMA_Disclaimer_CloseCookiesWindow(){
	CMA_Disclaimer_CreateCookie('cma_disclaimer', 'Y', 365);
    document.getElementById('disclaimer-message-container').removeChild(document.getElementById('disclaimer-message'));
    document.getElementById('disclaimer-message-container').parentNode.removeChild(document.getElementById('disclaimer-message-container'));
}
function CMA_Disclaimer_RejectCookiesWindow(){
    document.getElementById('disclaimer-message-container').removeChild(document.getElementById('disclaimer-message'));
    document.getElementById('disclaimer-message-container').parentNode.removeChild(document.getElementById('disclaimer-message-container'));
    window.location = '/';
}

window.onload = CMA_Disclaimer_CheckCookies;