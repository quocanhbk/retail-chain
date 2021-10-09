import 'package:flutter/foundation.dart';

class InvoiceReceivedWhenCreated {
  int? invoiceId;
  int? totalSellPrice;
  int? discount;
  DateTime? createdDatetime;
  String? status;
  int? customerId;
  String? customerName;
  int? sellerId;
  String? sellerName;

  InvoiceReceivedWhenCreated(
      {required String invoiceId,
      required String totalSellPrice,
      required String discount,
      required String createdDatetime,
      required String status,
      required String customerId,
      required String customerName,
      required String sellerId,
      required String sellerName}) {
    this.invoiceId =
        int.tryParse(invoiceId) == null ? 0 : int.parse(invoiceId);
    this.totalSellPrice =
        int.tryParse(totalSellPrice) == null ? 0 : int.parse(totalSellPrice);
    this.discount = int.tryParse(discount) == null ? 0 : int.parse(discount);
    this.createdDatetime = DateTime.tryParse(createdDatetime) == null
        ? DateTime.fromMicrosecondsSinceEpoch(0)
        : DateTime.parse(createdDatetime);
    if(status=="null"){
      this.status=null;
    }else{
      this.status = status;
    }
    this.customerId =
        int.tryParse(customerId) == null ? 0 : int.tryParse(customerId);
    if(customerName=="null"){
      this.customerName=null;
    }else{
      this.customerName = customerName;
    }
    this.sellerId = int.tryParse(sellerId) == null ? 0 : int.tryParse(sellerId);
    if(sellerName=="null"){
      this.sellerName=null;
    }else{
      this.sellerName = sellerName;
    }
  }
}

class DetailInvoiceInfo {
  InvoiceReceivedWhenCreated? invoiceInfo;
  List<Item> items;
  DetailInvoiceInfo(this.invoiceInfo, this.items);
}

class Item {
  late String name;
  int? sellPrice;
  int? quantity;
  Item({
    required String sellPrice,
    required String quantity,
    required String name,
  }) {
    this.name = name;
    this.sellPrice =
        int.tryParse(sellPrice) == null ? -1 : int.tryParse(sellPrice);
    this.quantity =
        int.tryParse(quantity) == null ? -1 : int.tryParse(quantity);
  }
}
