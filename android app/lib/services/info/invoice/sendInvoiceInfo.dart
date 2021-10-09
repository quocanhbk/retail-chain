import 'dart:convert';

import 'package:bkrm/services/cartService.dart';

class SendInvoiceItem {
  int? priceId;
  int? discount;
  int quantity;
  double pointRatio;
  SendInvoiceItem(this.priceId, this.discount, this.quantity,this.pointRatio);
  Map<String, dynamic> export() {
    return {"price_id": priceId, "quantity": quantity,"point_ratio":pointRatio};
  }
}

class SendInvoiceInfo {
  int? customerId;
  String? customerPhone;
  String status;
  int totalSellValue;
  int? customerPoint;
  int discount;
  int id = 0;
  List<SendInvoiceItem> invoiceItems = <SendInvoiceItem>[];
  SendInvoiceInfo(this.customerId,this.customerPhone, this.totalSellValue,this.discount,this.status, this.customerPoint,List<CartItem> items) {
    items.forEach((element) {
      invoiceItems.add(SendInvoiceItem(
          element.item.priceId,
          int.tryParse(element.discountPrice.toString()) == null
              ? 0
              : int.tryParse(element.discountPrice.toString()),
          element.amount,
      element.item.pointRatio));
    });
  }
  Map<String, dynamic> exportInvoice() {
    if(customerId!=-1){
      return {
        "invoice": {
          "customer_id": this.customerId,
          "status": status,
          "customer_point":customerPoint,
          "total_sell_price": totalSellValue,
          "discount": this.discount,
        },
        "invoice_items": exportInvoiceItems()
      };
    }else{
      return {
        "invoice": {
          "customer_phone": this.customerPhone,
          "status": status,
          "customer_point":customerPoint,
          "total_sell_price": totalSellValue,
          "discount": this.discount,
        },
        "invoice_items": exportInvoiceItems()
      };
    }
  }

  List<Map<String, dynamic>> exportInvoiceItems() {
    List<Map<String, dynamic>> tempList = [];
    invoiceItems.forEach((element) {
      tempList.add(element.export());
    });
    return tempList;
  }
}
