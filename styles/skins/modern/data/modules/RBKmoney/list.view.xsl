<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://common/">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <!-- Шаблон вкладки "Товары для регулярных платежей" -->
    <xsl:template
            match="/result[@method = 'recurrent_items']/data[@type = 'list' and @action = 'view']">
        <form action="../recurrent_items_save" method="post">
            <div>
                <span>&RBK_MONEY_ITEM_IDS;</span>
                <textarea name="items">
                    <xsl:value-of select="//data/items"/>
                </textarea>
            </div>

            <button type="submit" class="btn color-blue btn-small">&RBK_MONEY_SAVE;</button>
        </form>
    </xsl:template>

    <!-- Шаблон вкладки "Настройки" -->
    <xsl:template match="/result[@method = 'settings']/data[@type = 'list' and @action = 'view']">
        <xsl:param name="shopId" select="//data/settings/shopId"/>
        <xsl:param name="paymentType" select="//data/settings/paymentType"/>
        <xsl:param name="holdExpiration" select="//data/settings/holdExpiration"/>
        <xsl:param name="cardHolder" select="//data/settings/cardHolder"/>
        <xsl:param name="shadingCvv" select="//data/settings/shadingCvv"/>
        <xsl:param name="successStatus" select="//data/settings/successStatus"/>
        <xsl:param name="holdStatus" select="//data/settings/holdStatus"/>
        <xsl:param name="cancelStatus" select="//data/settings/cancelStatus"/>
        <xsl:param name="refundStatus" select="//data/settings/refundStatus"/>
        <xsl:param name="fiscalization" select="//data/settings/fiscalization"/>
        <xsl:param name="saveLogs" select="//data/settings/saveLogs"/>
        <form action="../save_settings" method="post">
            <table style="width: 50%">
                <tbody>
                    <tr>
                        <td>
                            &RBK_MONEY_API_KEY;
                        </td>
                        <td>
                            <textarea name="apiKey">
                                <xsl:value-of select="//data/settings/apiKey"/>
                            </textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            &RBK_MONEY_SHOP_ID;
                        </td>
                        <td>
                            <input name="shopId" style="width:auto;" class="default"
                                   value="{$shopId}"/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            &RBK_MONEY_PAYMENT_TYPE;
                        </td>
                        <td>
                            <select name="paymentType">
                                <xsl:choose>
                                    <xsl:when
                                            test="$paymentType = 'RBK_MONEY_PAYMENT_TYPE_INSTANTLY'">
                                        <option value="RBK_MONEY_PAYMENT_TYPE_HOLD">&RBK_MONEY_PAYMENT_TYPE_HOLD;</option>
                                        <option selected="true"
                                                value="RBK_MONEY_PAYMENT_TYPE_INSTANTLY">&RBK_MONEY_PAYMENT_TYPE_INSTANTLY;
                                        </option>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <option selected="true"
                                                value="RBK_MONEY_PAYMENT_TYPE_HOLD">&RBK_MONEY_PAYMENT_TYPE_HOLD;
                                        </option>
                                        <option value="RBK_MONEY_PAYMENT_TYPE_INSTANTLY">&RBK_MONEY_PAYMENT_TYPE_INSTANTLY;</option>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            &RBK_MONEY_HOLD_EXPIRATION;
                        </td>
                        <td>
                            <select name="holdExpiration">
                                <xsl:choose>
                                    <xsl:when test="$holdExpiration = 'RBK_MONEY_EXPIRATION_SHOP'">
                                        <option value="RBK_MONEY_EXPIRATION_PAYER">&RBK_MONEY_EXPIRATION_PAYER;</option>
                                        <option selected="true"
                                                value="RBK_MONEY_EXPIRATION_SHOP">&RBK_MONEY_EXPIRATION_SHOP;
                                        </option>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <option value="RBK_MONEY_EXPIRATION_PAYER">&RBK_MONEY_EXPIRATION_PAYER;</option>
                                        <option value="RBK_MONEY_EXPIRATION_SHOP">&RBK_MONEY_EXPIRATION_SHOP;</option>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            &RBK_MONEY_CARD_HOLDER;
                        </td>
                        <td>
                            <select name="cardHolder">
                                <xsl:choose>
                                    <xsl:when test="$cardHolder = 'RBK_MONEY_NOT_SHOW_PARAMETER'">
                                        <option value="RBK_MONEY_SHOW_PARAMETER">&RBK_MONEY_SHOW_PARAMETER;</option>
                                        <option selected="true"
                                                value="RBK_MONEY_NOT_SHOW_PARAMETER">&RBK_MONEY_NOT_SHOW_PARAMETER;
                                        </option>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <option value="RBK_MONEY_SHOW_PARAMETER">&RBK_MONEY_SHOW_PARAMETER;</option>
                                        <option value="RBK_MONEY_NOT_SHOW_PARAMETER">&RBK_MONEY_NOT_SHOW_PARAMETER;</option>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            &RBK_MONEY_SHADING_CVV;
                        </td>
                        <td>
                            <select name="shadingCvv">
                                <xsl:choose>
                                    <xsl:when test="$shadingCvv = 'RBK_MONEY_NOT_SHOW_PARAMETER'">
                                        <option value="RBK_MONEY_SHOW_PARAMETER">&RBK_MONEY_SHOW_PARAMETER;</option>
                                        <option selected="true"
                                                value="RBK_MONEY_NOT_SHOW_PARAMETER">&RBK_MONEY_NOT_SHOW_PARAMETER;
                                        </option>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <option value="RBK_MONEY_SHOW_PARAMETER">&RBK_MONEY_SHOW_PARAMETER;</option>
                                        <option value="RBK_MONEY_NOT_SHOW_PARAMETER">&RBK_MONEY_NOT_SHOW_PARAMETER;</option>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            &RBK_MONEY_SUCCESS_ORDER_STATUS;
                        </td>
                        <td>
                            <select name="successStatus">
                                <xsl:choose>
                                    <xsl:when
                                            test="$successStatus = 'accepted'">
                                        <option value="accepted">&RBK_MONEY_STATUS_ACCEPTED;</option>
                                        <option selected="true"
                                                value="ready">&RBK_MONEY_STATUS_READY;
                                        </option>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <option value="ready">&RBK_MONEY_STATUS_READY;</option>
                                        <option value="accepted">&RBK_MONEY_STATUS_ACCEPTED;</option>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            &RBK_MONEY_HOLD_ORDER_STATUS;
                        </td>
                        <td>
                            <select name="holdStatus">
                                <xsl:choose>
                                    <xsl:when
                                            test="$holdStatus = 'accepted'">
                                        <option selected="true" value="accepted">&RBK_MONEY_STATUS_ACCEPTED;</option>
                                        <option value="ready">&RBK_MONEY_STATUS_READY;
                                        </option>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <option value="ready">&RBK_MONEY_STATUS_READY;</option>
                                        <option value="accepted">&RBK_MONEY_STATUS_ACCEPTED;</option>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            &RBK_MONEY_CANCEL_ORDER_STATUS;
                        </td>
                        <td>
                            <select name="cancelStatus">
                                <xsl:choose>
                                    <xsl:when
                                            test="$cancelStatus = 'rejected'">
                                        <option selected="true" value="rejected">&RBK_MONEY_STATUS_REJECTED;</option>
                                        <option value="canceled">&RBK_MONEY_STATUS_CANCELLED;
                                        </option>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <option value="canceled">&RBK_MONEY_STATUS_CANCELLED;</option>
                                        <option value="rejected">&RBK_MONEY_STATUS_REJECTED;</option>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            &RBK_MONEY_REFUND_ORDER_STATUS;
                        </td>
                        <td>
                            <select name="refundStatus">
                                <xsl:choose>
                                    <xsl:when
                                            test="$refundStatus = 'rejected'">
                                        <option selected="true" value="rejected">&RBK_MONEY_STATUS_REJECTED;</option>
                                        <option value="canceled">&RBK_MONEY_STATUS_CANCELLED;
                                        </option>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <option value="canceled">&RBK_MONEY_STATUS_CANCELLED;</option>
                                        <option value="rejected">&RBK_MONEY_STATUS_REJECTED;</option>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            &RBK_MONEY_FISCALIZATION;
                        </td>
                        <td>
                            <select name="fiscalization">
                                <xsl:choose>
                                    <xsl:when
                                            test="$fiscalization = 'RBK_MONEY_NOT_SHOW_PARAMETER'">
                                        <option value="RBK_MONEY_SHOW_PARAMETER">&RBK_MONEY_SHOW_PARAMETER;</option>
                                        <option selected="true"
                                                value="RBK_MONEY_NOT_SHOW_PARAMETER">&RBK_MONEY_NOT_SHOW_PARAMETER;
                                        </option>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <option value="RBK_MONEY_SHOW_PARAMETER">&RBK_MONEY_SHOW_PARAMETER;</option>
                                        <option value="RBK_MONEY_NOT_SHOW_PARAMETER">&RBK_MONEY_NOT_SHOW_PARAMETER;</option>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            &RBK_MONEY_SAVE_LOGS;
                        </td>
                        <td>
                            <select name="saveLogs">
                                <xsl:choose>
                                    <xsl:when test="$saveLogs = 'RBK_MONEY_NOT_SHOW_PARAMETER'">
                                        <option value="RBK_MONEY_SHOW_PARAMETER">&RBK_MONEY_SHOW_PARAMETER;</option>
                                        <option selected="true"
                                                value="RBK_MONEY_NOT_SHOW_PARAMETER">&RBK_MONEY_NOT_SHOW_PARAMETER;
                                        </option>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <option value="RBK_MONEY_SHOW_PARAMETER">&RBK_MONEY_SHOW_PARAMETER;</option>
                                        <option value="RBK_MONEY_NOT_SHOW_PARAMETER">&RBK_MONEY_NOT_SHOW_PARAMETER;</option>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
            <button type="submit" class="btn color-blue btn-small">&RBK_MONEY_SAVE;</button>
        </form>
    </xsl:template>

    <!--Шаблон вкладкки Транзакции-->
    <xsl:template match="/result[@method = 'page_transactions']/data[@type = 'list' and @action = 'view']">
        <xsl:param name="fromDate" select="//data/date_from"/>
        <xsl:param name="toDate" select="//data/date_to"/>
        <xsl:param name="page" select="//data/page"/>
        <script type="text/javascript" src="/styles/skins/modern/design/js/rbkmoney.js"/>
        <div class="datePicker">
            <span style="width:4%; display: inline-block;">&RBK_MONEY_DATE_FILTER_FROM;</span>
            <input type="text" style="width:auto; display: inline-block;" class="default"
                   value="{$fromDate}"
                   id="date_from"/>
        </div>
        <div class="datePicker">
            <span style="width:4%; display: inline-block;">&RBK_MONEY_DATE_FILTER_TO;</span>
            <input type="text" style="width:auto; display: inline-block;" class="default"
                   value="{$toDate}"
                   id="date_to"/>
        </div>
        <button id="transactions_filter"
                class="btn color-blue btn-small">&RBK_MONEY_FILTER_SUBMIT;
        </button>
        <div>
            <input id="currentPage" type="hidden" value="{$page}"/>
            <table class="tableContent btable btable-bordered btable-striped"
                   id="transactionsTable">
                <thead>
                    <tr>
                        <th>&RBK_MONEY_TRANSACTION_ID;</th>
                        <th>&RBK_MONEY_TRANSACTION_PRODUCT;</th>
                        <th>&RBK_MONEY_TRANSACTION_STATUS;</th>
                        <th>&RBK_MONEY_TRANSACTION_AMOUNT;</th>
                        <th>&RBK_MONEY_TRANSACTION_CREATED_AT;</th>
                        <th>&RBK_MONEY_ACTIONS;</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <table id="pages">
                <tbody>
                </tbody>
            </table>
        </div>
    </xsl:template>

    <!--Шаблон вкладкки Регулярные платежи-->
    <xsl:template match="/result[@method = 'page_recurrent']/data[@type = 'list' and @action = 'view']">
        <script type="text/javascript" src="/styles/skins/modern/design/js/rbkmoney.js"/>
        <div>
            <table class="tableContent btable btable-bordered btable-striped" id="recurrentTable">
                <thead>
                    <tr>
                        <th>&RBK_MONEY_USER_FIELD;</th>
                        <th>&RBK_MONEY_AMOUNT_FIELD;</th>
                        <th>&RBK_MONEY_PRODUCT_FIELD;</th>
                        <th>&RBK_MONEY_TRANSACTION_STATUS;</th>
                        <th>&RBK_MONEY_RECURRENT_CREATE_DATE;</th>
                        <th>&RBK_MONEY_ACTIONS;</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </xsl:template>

    <!--Шаблон вкладкки Логи запросов RBKMoney-->
    <xsl:template match="/result[@method = 'logs']/data[@type = 'list' and @action = 'view']">
        <div>
            <textarea>
                <xsl:value-of select="//data/logs"/>
            </textarea>
            <table>
                <tr>
                    <th>
                        <form action='../deleteLogs'>
                            <button class="btn color-blue btn-small" type='submit'>&RBK_MONEY_DELETE_LOGS;</button>
                        </form>
                    </th>
                    <th>
                        <form action='../downloadLogs'>
                            <button class="btn color-blue btn-small" type='submit'>&RBK_MONEY_DOWNLOAD_LOGS;</button>
                        </form>
                    </th>
                </tr>
            </table>
        </div>
    </xsl:template>

    <!--Шаблон страницы ошибки-->
    <xsl:template match="/result[@method = 'page_transactions']/data[@type = 'error' and @action = 'view']">
        <xsl:value-of select="//data/message"/>
    </xsl:template>

</xsl:stylesheet>
