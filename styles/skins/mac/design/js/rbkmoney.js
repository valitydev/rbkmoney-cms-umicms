$(document).ready(function () {
    var transactionsTable = jQuery('#transactionsTable').length;
    var recurrentTable = jQuery('#recurrentTable').length;

    if (transactionsTable > 0) {
        getTable('#transactionsTable');

        $('#transactions_filter').click(function () {
            getTable('#transactionsTable');
        });
    }

    if (recurrentTable > 0) {
        getTable('#recurrentTable');
    }
});

function getTable(tableName) {
    var url;

    if ('#transactionsTable' == tableName) {
        var fromDate = new Date(jQuery('#date_from').val());
        var toDate = new Date(jQuery('#date_to').val());
        var page = jQuery('#currentPage').val();

        fromDate = ((fromDate / 1 + fromDate.getTimezoneOffset() * 60000) / 1000 - (fromDate % 1));
        toDate = ((toDate / 1 + toDate.getTimezoneOffset() * 60000) / 1000 - (toDate % 1));

        url = '/admin/RBKmoney/getTransactions?page=' + page + '&date_from=' + fromDate + '&date_to=' + toDate + '&random=' + Math.random();
    } else {
        url = '/admin/RBKmoney/getRecurrent?&random=' + Math.random();
    }

    jQuery.ajax({
        url: url,
        async: true,
        dataType: 'json',
        timeout: 30000,

        beforeSend: function () {
            jQuery(jQuery(tableName).find('tbody')).append('<tr><td colspan="6" style="text-align:center"><img src="/images/cms/admin/mac/table/loading.gif" /></td></tr>');
        },
        success: function (data) {
            var item;
            var val;
            jQuery(tableName).find('tbody').find('tr').remove();

            if (data.result.error.length > 0) {
                jQuery(jQuery(tableName).find('tbody')).append('<tr><td colspan="6" style="text-align:center"><span class="runOneStat">'+data.result.error+'</span></td></tr>');
            }

            if ('#transactionsTable' == tableName) {
                if (data.result.transactions.length > 0) {
                    for (item in data.result.transactions) {
                        val = data.result.transactions[item];

                        jQuery(tableName).find('tbody').append(
                            '<tr>' +
                            '<td align="center">' + val.orderId + '</td>' +
                            '<td align="center">' + val.product + '</td>' +
                            '<td align="center">' + val.status + '</td>' +
                            '<td align="center">' + val.amount + '</td>' +
                            '<td align="center">' + val.createdAt + '</td>' +
                            '<td align="center" width="12%">' + val.button + '</td>' +
                            '</tr>'
                        )
                    }
                }

                if (data.result.pages.length > 0) {
                    var tbody = jQuery('#pages').find('tbody');
                    tbody.find('tr').remove();
                    tbody.find('tbody').append(
                        '<tr>' +
                        '<td>' + data.result.pages + '</td>' +
                        '</tr>'
                    )
                }
            } else {
                if (data.result.recurrent.length > 0) {
                    for (item in data.result.recurrent) {
                        val = data.result.recurrent[item];

                        jQuery(tableName).find('tbody').append(
                            '<tr>' +
                            "<td align='center'><a href='"+val.user+"'>"+val.userName+"</a></td>" +
                            '<td align="center">' + val.amount + '</td>' +
                            '<td align="center">' + val.name + '</td>' +
                            '<td align="center">' + val.status + '</td>' +
                            '<td align="center">' + val.date + '</td>' +
                            '<td align="center" width="5%">' +
                                '<form action="../deleteRecurrent">' +
                                    '<input type="hidden" name="recurrentId" value="'+ val.recurrentId +'">' +
                                    '<button type="submit" ' +
                                        'class="btn color-blue btn-small">'+ val.buttonName +
                                    '</button>' +
                                '</form>' +
                            '</td>' +
                            '</tr>'
                        )
                    }
                }
            }
        },
        error: function () {
            jQuery(tableName).find('tbody').find('tr').remove();
            jQuery(jQuery(tableName).find('tbody')).append('<tr><td colspan="6" style="text-align:center"><span class="runOneStat">error</span></td></tr>');
        }
    });
}
