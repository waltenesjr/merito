function statusCarregando(acao){
    $('.carregando').css('display',acao);
}

function arrayToObject(array){
    var retorno = new Object();
                    
    $.map( array, function( obj ) {
        retorno[obj.name] = obj.value;
    });
    
    return retorno;
}

function tratarMsgRetorno(dados){
    $.each( dados, function( key, obj ) {
        if(key == 'alerta' || key == 'sucesso' || key == 'erro' || key == 'informacao' || key == 'atencao')
            $.map( obj, function( o ) {
                var type = 'error';
                if(key == 'alerta' || key == 'atencao')
                    type = 'warning';
                else if(key == 'sucesso')
                    type = 'success';
                else if(key == 'erro')
                    type = 'error';
                else if(key == 'informacao')
                    type = 'info';
                console.log(key);
                $.toast({
                    heading: key[0].toUpperCase()+key.slice(1),
                    text: o,
                    position: 'top-right',
                    icon: type
                });
            });
    });
}

function toMes(mes){
    var index = parseInt(mes);
    
    var arr = new Array(
                'Janeiro',
                'Fevereiro',
                'Março',
                'Abril',
                'Maio',
                'Junho',
                'Julho',
                'Agosto',
                'Setembro',
                'Outubro',
                'Novembro',
                'Dezembro'
            );

    return arr[index];
}

function _getFieldsContainer(container){
    var fields = $(container).serializeArray();
    var dados = new Object();
                    
    $.map( fields, function( obj ) {
        if (obj.value) {
            dados[obj.name] = obj.value;
        }
    });
    
    return dados;
}

function validarEmail(email){
    var exclude=/[^@\-\.\w]|^[_@\.\-]|[\._\-]{2}|[@\.]{2}|(@)[^@]*\1/;
    var check=/@[\w\-]+\./;
    var checkend=/\.[a-zA-Z]{2,3}$/;
    if(((email.search(exclude) != -1)||(email.search(check)) == -1)||(email.search(checkend) == -1)){return false;}
    else {return true;}
}

function validarCpf(cpf){
    cpf = soNumeros(cpf);
    var numeros, digitos, soma, i, resultado, digitos_iguais;
    digitos_iguais = 1;
    if (cpf.length < 11)
          return false;
    for (i = 0; i < cpf.length - 1; i++)
          if (cpf.charAt(i) != cpf.charAt(i + 1))
                {
                digitos_iguais = 0;
                break;
                }
    if (!digitos_iguais)
          {
          numeros = cpf.substring(0,9);
          digitos = cpf.substring(9);
          soma = 0;
          for (i = 10; i > 1; i--)
                soma += numeros.charAt(10 - i) * i;
          resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
          if (resultado != digitos.charAt(0))
                return false;
          numeros = cpf.substring(0,10);
          soma = 0;
          for (i = 11; i > 1; i--)
                soma += numeros.charAt(11 - i) * i;
          resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
          if (resultado != digitos.charAt(1))
                return false;
          return true;
          }
    else
          return false;
}

function valida_cnpj(cnpj)
      {
      var numeros, digitos, soma, i, resultado, pos, tamanho, digitos_iguais;
      digitos_iguais = 1;
      if (cnpj.length < 14 && cnpj.length < 15)
            return false;
      for (i = 0; i < cnpj.length - 1; i++)
            if (cnpj.charAt(i) != cnpj.charAt(i + 1))
                  {
                  digitos_iguais = 0;
                  break;
                  }
      if (!digitos_iguais)
            {
            tamanho = cnpj.length - 2
            numeros = cnpj.substring(0,tamanho);
            digitos = cnpj.substring(tamanho);
            soma = 0;
            pos = tamanho - 7;
            for (i = tamanho; i >= 1; i--)
                  {
                  soma += numeros.charAt(tamanho - i) * pos--;
                  if (pos < 2)
                        pos = 9;
                  }
            resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
            if (resultado != digitos.charAt(0))
                  return false;
            tamanho = tamanho + 1;
            numeros = cnpj.substring(0,tamanho);
            soma = 0;
            pos = tamanho - 7;
            for (i = tamanho; i >= 1; i--)
                  {
                  soma += numeros.charAt(tamanho - i) * pos--;
                  if (pos < 2)
                        pos = 9;
                  }
            resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
            if (resultado != digitos.charAt(1))
                  return false;
            return true;
            }
      else
            return false;
      } 


function soNumeros(vlr){
    var expre = /[^0-9]/g;
    return vlr.replace(expre,'');
}
/*
$(function(){   
    
    $("#menuPrincipal").html('<div id="menu-principal"></div>');
    $("#menu-principal").kendoMenu({
        dataSource: [{
                text: "Unidades",
                imageUrl: "/imagens/unidades.png",
                value: "unidades"
            },
			{
				text: 'Colaboradores',
				imageUrl: "/imagens/profissionais.png",
				cssClass: "subItemMenuPrincipal",
				value: "profissionais/index/profissionais_tipo/colaborador"
			},
            {
                text: "Tabela de Avaliação",
				imageUrl: "/imagens/tabela.png",
                value: "tabela-avaliacao"
            },
			{
				text: 'Profissionais',
				imageUrl: "/imagens/profissionais.png",
				cssClass: "subItemMenuPrincipal",
				value: "autonomos/index/autonomos_tipo/autonomo"
			},
            {
                text: "Tabela de Avaliação Profissionais",
				imageUrl: "/imagens/tabela.png",
                value: "tabela-avaliacao-autonomos"
            },
            {
                text: "Relatórios",
				imageUrl: "/imagens/relatorios.png",
                value: "relatorios"
            },
            {
                text: "Documentos",
				imageUrl: "/imagens/documentos.png",
                cssClass: "subItemMenuPrincipal",
                items:[
                    {
                        text: "Colaboradoes",
                        cssClass: "subItemMenuPrincipal",
                        items:[
                            {text: "Gerência",cssClass: "subItemMenuPrincipal", url: "/documentos/gerencia.pdf"},
                            {text: "Supervisão de atendimento",cssClass: "subItemMenuPrincipal", url: "/documentos/supervisao.pdf"},
							{text: "Operador de Caixa", cssClass: "subItemMenuPrincipal", url: "/documentos/operadorcaixa.pdf"},
                            {text: "Recepção",cssClass: "subItemMenuPrincipal", url: "/documentos/recepcao.pdf"},
                            {text: "Telefonia",cssClass: "subItemMenuPrincipal", url: "/documentos/telefonia.pdf"},
                            {text: "Atendente de Color bar",cssClass: "subItemMenuPrincipal", url: "/documentos/colorbar.pdf"},
                            {text: "Serviços de Copa",cssClass: "subItemMenuPrincipal", url: "/documentos/copa.pdf"},
                            {text: "Manobrista",cssClass: "subItemMenuPrincipal", url: "/documentos/manobrista.pdf"},
                            {text: "Serviços gerais",cssClass: "subItemMenuPrincipal", url: "/documentos/gerais.pdf"}
                        ]
                    },
                    {
                        text: "Grupo Su Beauty",
                        cssClass: "subItemMenuPrincipal",
                        items:[
                            {text: "Corpore",cssClass: "subItemMenuPrincipal", url: "/documentos/grupo/corpore.pdf"},
                            {text: "Dep. Autônomos", cssClass: "subItemMenuPrincipal", url: "/documentos/grupo/autonomos.pdf"},
							{text: "Dep. Pessoal", cssClass: "subItemMenuPrincipal", url: "/documentos/grupo/dppessoal.pdf"},
							{text: "Gestão Administrativa",cssClass: "subItemMenuPrincipal", url: "/documentos/grupo/adm.pdf"},
                            {text: "Gestão de Marketing",cssClass: "subItemMenuPrincipal", url: "/documentos/grupo/marketing.pdf"},
                            {text: "Gestão Comercial",cssClass: "subItemMenuPrincipal", url: "/documentos/grupo/comercial.pdf"},
                            {text: "Gestão Financeira",cssClass: "subItemMenuPrincipal", url: "/documentos/grupo/financeiro.pdf"},
                            {text: "CETECAP/Recursos Humanos",cssClass: "subItemMenuPrincipal", url: "/documentos/grupo/rh.pdf"},
                            {text: "Academia Hair School",cssClass: "subItemMenuPrincipal", url: "/documentos/grupo/academia.pdf"}
                        ]
                    },
                    {
                        text: "Código de Ética",
                        cssClass: "subItemMenuPrincipal",
                        url: "/documentos/codigo_etica.pdf"
                    }
                ]
            },
            {
                text: "Sair",
		imageUrl: "/imagens/sair.png",
                value: "login/logout"
            }],
        select: function(e){
            //Limpa e Adicionando os botões no local indicado
            $("#moduloCarregado").html('');
            $("#botoesModulo").html('');
            $("#contentPrincipal").html('');
            
            
            //Primeiro remove a classe do item que foi selecionado e depois adiciona ao item que esta sendo selecionado
            this.element.find(".menuItemSelecionado").removeClass('menuItemSelecionado');
            $(e.item).addClass('menuItemSelecionado');
            
            //Tratamento para carregar o módulo solicitado
            var item = $(e.item),
                menuElement = item.closest(".k-menu"),
                dataItem = this.options.dataSource,
                index = item.parentsUntil(menuElement, ".k-item").map(function () {
                    return $(this).index();
                }).get().reverse();

            index.push(item.index());

            for (var i = -1, len = index.length; ++i < len;) {
                dataItem = dataItem[index[i]];
                dataItem = i < len-1 ? dataItem.items : dataItem;
            }
            
            if(dataItem.value != undefined){
                $.ajax(
                    {
                        url:dataItem.value+'/',
                        dataType:'html',
                        type:'POST',
                        beforeSend:function(){
                            statusCarregando('');
                        },
                        success: function(data, textStatus){
                            var permissao = null;
                            if(data.substring(0,8) == '{"status')
                                permissao = $.parseJSON(data);

							

                            if(permissao == null){
                                var controller = e['item']['textContent'].toLowerCase();
                                
                                //alert (controller);
                                
                                if(controller != "sair"){
                                    $("#contentPrincipal").html('');
                                    if(e['item']['textContent'] != "Sair")
                                    $("#moduloCarregado").html(e['item']['textContent']);
                                    $("#contentPrincipal").html(data);
                                    statusCarregando('none');
                                }else{
                                    var obj = $.parseJSON(data);
                                    if(dataItem.value == "login/logout" && obj.dados == "desconectado")
                                        window.location.href = '/login';
                                }
                            }else{
                                $().msgbox("alerta", permissao.mensagem);
                            }
                        },
                        complete: function() {
                            statusCarregando('none');
                        },
                        error: function(xhr, er){
                            statusCarregando('none');
                        }
                    }
                );
            }
        }
    });
    
    
    kendo.cultures["pt-BR"] = {
        //<language code>-<country/region code>
        name: "pt-BR",
        // "numberFormat" defines general number formatting rules
        numberFormat: {
            //numberFormat has only negative pattern unline the percent and currency
            //negative pattern: one of (n)|-n|- n|n-|n -
            pattern: ["-n"],
            //number of decimal places
            decimals: 2,
            //string that separates the number groups (1,000,000)
            ",": ",",
            //string that separates a number from the fractional point
            ".": ".",
            //the length of each number group
            groupSize: [3],
            //formatting rules for percent number
            percent: {
                //[negative pattern, positive pattern]
                //negativePattern: one of -n %|-n%|-%n|%-n|%n-|n-%|n%-|-% n|n %-|% n-|% -n|n- %
                //positivePattern: one of n %|n%|%n|% n
                pattern: ["-n %", "n %"],
                //number of decimal places
                decimals: 2,
                //string that separates the number groups (1,000,000 %)
                ".": ".",
                //string that separates a number from the fractional point
                ",": ",",
                //the length of each number group
                groupSize: [3],
                //percent symbol
                symbol: "%"
            },
            currency: {
                //[negative pattern, positive pattern]
                //negativePattern: one of "($n)|-$n|$-n|$n-|(n$)|-n$|n-$|n$-|-n $|-$ n|n $-|$ n-|$ -n|n- $|($ n)|(n $)"
                //positivePattern: one of "$n|n$|$ n|n $"
                pattern: ["($n)", "$n"],
                //number of decimal places
                decimals: 2,
                //string that separates the number groups (1,000,000 $)
                ".": ".",
                //string that separates a number from the fractional point
                ",": ",",
                //the length of each number group
                groupSize: [3],
                //currency symbol
                symbol: "R$"
            }
        },
        calendars: {
            standard: {
                days: {
                    // full day names
                    names: ["Domingo", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado"],
                    // abbreviated day names
                    namesAbbr: ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "SAb"],
                    // shortest day names
                    namesShort: [ "Do", "Se", "Te", "Qa", "Qi", "Se", "Sa" ]
                },
                months: {
                    // full month names
                    names: ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"],
                    // abbreviated month names
                    namesAbbr: ["Jan", "Feb", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"]
                },
                // AM and PM designators
                // [standard,lowercase,uppercase]
                AM: [ "AM", "am", "AM" ],
                PM: [ "PM", "pm", "PM" ],
                // set of predefined date and time patterns used by the culture.
                patterns: {
                    d: "dd/MM/yyyy",
                    D: "dddd, MMMM dd, yyyy",
                    F: "dddd, MMMM dd, yyyy h:mm:ss tt",
                    g: "dd/MM/yyyy h:mm tt",
                    G: "dd/MM/yyyy h:mm:ss tt",
                    m: "MMMM dd",
                    M: "MMMM dd",
                    s: "yyyy'-'MM'-'ddTHH':'mm':'ss",
                    t: "h:mm tt",
                    T: "h:mm:ss tt",
                    u: "yyyy'-'MM'-'dd HH':'mm':'ss'Z'",
                    y: "MMMM, yyyy",
                    Y: "MMMM, yyyy"
                },
                // the first day of the week (0 = Sunday, 1 = Monday, etc)
                firstDay: 0
            }
        }
    };
});
*/

