import 'package:bkrm/services/info/invoice/sendInvoiceInfo.dart';
import 'package:flutter/foundation.dart';

class RefundSheet {
  int? refundSheetId;
  int? invoiceId;
  int? refunderId;
  String? refunderName;
  int? customerId;
  String? customerName;
  String? customerPhone;
  int? totalRefundPrice;
  String? reason;
  DateTime? createdDatetime;

  RefundSheet(
      {required String refundSheetId,
      required String invoiceId,
      required String refunderId,
        required String refunderName,
      required String customerId,
      required String customerName,
      required String customerPhone,
      required String totalRefundPrice,
        required String reason,
      required String createdDatetime}) {
    this.refundSheetId=int.tryParse(refundSheetId)==null?-1:int.tryParse(refundSheetId);
    this.invoiceId=int.tryParse(invoiceId)==null?-1:int.tryParse(invoiceId);
    this.refunderId=int.tryParse(refunderId)==null?-1:int.tryParse(refunderId);
    this.refunderName=refunderName;//Must have
    this.customerId=int.tryParse(customerId)==null?-1:int.tryParse(customerId);
    if(customerName=="null"){
      this.customerName=null;
    }else{
      this.customerName=customerName;
    }
    this.customerPhone=customerPhone;//Must have
    this.totalRefundPrice=int.tryParse(totalRefundPrice)==null?-1:int.tryParse(totalRefundPrice);
    if(reason=="null"){
      this.reason=null;
    }else{
      this.reason=reason;
    }
    this.createdDatetime=DateTime.tryParse(createdDatetime)==null?DateTime.fromMicrosecondsSinceEpoch(0):DateTime.tryParse(createdDatetime);
  }
}

class DetailRefundSheet {
  RefundSheet refundInfo;
  List<RefundItem> refundItems;
  DetailRefundSheet(this.refundInfo, this.refundItems);
}

class RefundItem {
  int? refundSheetId;
  int? refundItemId;
  int? itemId;
  late String name;
  int? sellPrice;
  int? quantity;
  String? imageUrl;
  RefundItem(
      {required String refundSheetId,
      required String refundItemId,
      required String sellPrice,
      required String quantity,
      required String itemId,
      required String name,
      required String imageUrl}) {
    this.refundSheetId =
        int.tryParse(refundSheetId) == null ? -1 : int.tryParse(refundSheetId);
    this.refundItemId =
        int.tryParse(refundItemId) == null ? -1 : int.tryParse(refundItemId);
    this.sellPrice =
        int.tryParse(sellPrice) == null ? -1 : int.tryParse(sellPrice);
    this.quantity =
        int.tryParse(quantity) == null ? -1 : int.tryParse(quantity);
    this.itemId = int.tryParse(itemId) == null ? -1 : int.tryParse(itemId);
    this.name = name;
    this.imageUrl = imageUrl;
  }
}
