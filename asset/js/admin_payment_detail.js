(function () {
    var data = window.ohnousPaymentDetail || {};
    if (!window.ApexCharts) return;
    var common = {chart:{fontFamily:'inherit',toolbar:{show:false},animations:{enabled:true}},colors:['#6775d6','#e6a23c','#2f9e6f'],dataLabels:{enabled:false},legend:{position:'bottom'},stroke:{width:2},responsive:[{breakpoint:768,options:{legend:{position:'bottom'}}}]};
    var amount = document.querySelector('#payment_amount_chart');
    if (amount) new ApexCharts(amount,Object.assign({},common,{chart:{type:'donut',height:260,toolbar:{show:false}},series:[Number(data.ht||0),Number(data.fee||0)],labels:['Montant HT','Supplément 10 %']})).render();
    var status = document.querySelector('#payment_status_chart');
    if (status) new ApexCharts(status,Object.assign({},common,{chart:{type:'line',height:260,toolbar:{show:false}},series:[{name:'État',data:[1,2]}],xaxis:{categories:['Créé','Statut actuel']},yaxis:{labels:{show:false},min:0,max:3},tooltip:{y:{formatter:function(){return data.status||'';}}}})).render();
})();
