import 'package:bkrm/services/info/invoice/invoiceReceivedWhenCreated.dart' as invoiceCreated;
import 'package:bkrm/services/info/invoice/sendInvoiceInfo.dart';
import 'package:flutter/foundation.dart';

import 'invoiceReceivedWhenCreated.dart';

class InvoiceReceivedWhenGet {
  int? invoiceId;
  int? branchId;
  int? sellerId;
  String? sellerName;
  int? customerId;
  String? customerName;
  String? customerPhone;
  int? totalSellPrice;
  int? discount;
  DateTime? createdDatetime;
  String? status;

  InvoiceReceivedWhenGet(
      {required String invoiceId,
      required String branchId,
      required String sellerId,
      required String sellerName,
      required String customerId,
      required String customerName,
      required String customerPhone,
      required String totalSellPrice,
      required String discount,
      required String createdDatetime,
      required String status}) {
    this.invoiceId =
        int.tryParse(invoiceId) == null ? 0 : int.tryParse(invoiceId);
    this.branchId = int.tryParse(branchId) == null ? 0 : int.tryParse(branchId);
    this.sellerId = int.tryParse(sellerId) == null ? 0 : int.tryParse(sellerId);
    if(sellerName=="null"){
      this.sellerName=null;
    }else{
      this.sellerName = sellerName;
    }
    this.customerId =
        int.tryParse(customerId) == null ? 0 : int.tryParse(customerId);
    if(customerName=="null"){
      this.customerName=null;
    }else{
      this.customerName = customerName;
    }
    if(customerPhone=="null"){
      this.customerPhone=null;
    }else{
      this.customerPhone = customerPhone;
    }

    this.totalSellPrice =
        int.tryParse(totalSellPrice) == null ? 0 : int.tryParse(totalSellPrice);
    this.discount = int.tryParse(discount) == null ? 0 : int.tryParse(discount);
    this.createdDatetime = DateTime.tryParse(createdDatetime) == null
        ? DateTime.fromMicrosecondsSinceEpoch(0)
        : DateTime.tryParse(createdDatetime);
    if(status=="null"){
      this.status=null;
    }else{
      this.status = status;
    }
  }

}

class DetailInvoiceInfo {
  InvoiceReceivedWhenGet invoiceInfo;
  List<Item> items;
  DetailInvoiceInfo(this.invoiceInfo, this.items);
  invoiceCreated.DetailInvoiceInfo toInvoiceWhenCreated(){
    InvoiceReceivedWhenCreated invoice = InvoiceReceivedWhenCreated(invoiceId: invoiceInfo.invoiceId.toString(), totalSellPrice: invoiceInfo.totalSellPrice.toString(),
        discount: invoiceInfo.discount.toString(),
        createdDatetime: invoiceInfo.createdDatetime.toString(), status: invoiceInfo.status.toString(),
        customerId: invoiceInfo.customerId.toString(), customerName: invoiceInfo.customerName.toString(),
        sellerId: invoiceInfo.sellerId.toString(), sellerName: invoiceInfo.sellerName.toString());
    invoiceCreated.DetailInvoiceInfo detailInvoice = invoiceCreated.DetailInvoiceInfo(invoice,items.map((e) {
      return invoiceCreated.Item(sellPrice: e.sellPrice.toString(), quantity: e.quantity.toString(), name: e.name);
    }).toList());
    return detailInvoice;
  }
}

class Item {
  int? invoiceId;
  int? invoiceItemId;
  int? sellPrice;
  int? quantity;
  int? itemId;
  late double pointRatio;
  late String name;
  String? imageUrl;
  Item(
      {required String invoiceId,
      required String invoiceItemId,
      required String sellPrice,
      required String quantity,
      required String itemId,
      required String name,
      required String imageUrl,
      required String pointRatio}) {
    this.invoiceId =
        int.tryParse(invoiceId) == null ? -1 : int.tryParse(invoiceId);
    this.invoiceItemId =
        int.tryParse(invoiceItemId) == null ? -1 : int.tryParse(invoiceItemId);
    this.sellPrice =
        int.tryParse(sellPrice) == null ? -1 : int.tryParse(sellPrice);
    this.quantity =
        int.tryParse(quantity) == null ? -1 : int.tryParse(quantity);
    this.itemId = int.tryParse(itemId) == null ? -1 : int.tryParse(itemId);
    this.name = name;
    this.imageUrl = imageUrl;
    this.pointRatio=double.tryParse(pointRatio)??0;
  }


}
