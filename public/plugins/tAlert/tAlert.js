//Alertas personalizados
//Tipos de de mensagem: sucesso, erro, informacao e atencao
//Exemplo de uso: $().msgbox('atencao', 'Favor informar os dados solicitados.');

(function($)
{
    var contShowAP = 0;
    var metodos = {
        sucesso: function (texto, fixar){
            var id = "divalertapersonalizado_"+(contShowAP++);
            var box = "<div class='alerta-personalizado-alerta ap-alerta-sucesso' id='"+id+"'><button class='alerta-personalizado-fechar' title='Fechar' onclick='$().msgbox(\"fechar\",\""+id+"\")'>×</button><strong>Sucesso!</strong><br/>"+texto+"<div>";
            metodos.mostrarMsgBox(box,id, fixar);
        },
        erro: function (texto, fixar){
            var id = "divalertapersonalizado_"+(contShowAP++);
            var box = "<div class='alerta-personalizado-alerta ap-alerta-erro' id='"+id+"'><button class='alerta-personalizado-fechar' title='Fechar' onclick='$().msgbox(\"fechar\",\""+id+"\")'>×</button><strong>Erro!</strong><br/>"+texto+"<div>";
            metodos.mostrarMsgBox(box,id, fixar);
        },
        informacao: function (texto, fixar){
            var id = "divalertapersonalizado_"+(contShowAP++);
            var box = "<div class='alerta-personalizado-alerta ap-alerta-informacao' id='"+id+"'><button class='alerta-personalizado-fechar' title='Fechar' onclick='$().msgbox(\"fechar\",\""+id+"\")'>×</button><strong>Informação!</strong><br/>"+texto+"<div>";
            metodos.mostrarMsgBox(box,id, fixar);
        },
        atencao: function (texto, fixar){
            var id = "divalertapersonalizado_"+(contShowAP++);
            var box = "<div class='alerta-personalizado-alerta ap-alerta-atencao' id='"+id+"'><button class='alerta-personalizado-fechar' title='Fechar' onclick='$().msgbox(\"fechar\",\""+id+"\")'>×</button><strong>Atenção!</strong><br/>"+texto+"<div>";
            metodos.mostrarMsgBox(box, id, fixar);
        },
        alerta: function (texto, fixar){
            var id = "divalertapersonalizado_"+(contShowAP++);
            var box = "<div class='alerta-personalizado-alerta ap-alerta-atencao' id='"+id+"'><button class='alerta-personalizado-fechar' title='Fechar' onclick='$().msgbox(\"fechar\",\""+id+"\")'>×</button><strong>Atenção!</strong><br/>"+texto+"<div>";
            metodos.mostrarMsgBox(box, id, fixar);
        },
        mostrarMsgBox: function (box, id, fixo){
            $("#alerta-personalizado").prepend(box);
            $( "#"+id ).animate({opacity: 1});
            if(fixo != true){
                setTimeout(function() {
                    metodos.fechar(id);
                }, 3000);
            }
        },
        fechar: function (id){
            $( "#"+id ).animate({opacity: 0}).remove();
        }
    }
    
    $.fn.msgbox = function( acao ) {
        metodos[acao].apply( this, Array.prototype.slice.call( arguments, 1 ));
    };
})(jQuery);