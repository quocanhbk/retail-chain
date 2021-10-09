import 'dart:async';

import 'package:bkrm/main.dart';
import 'package:bkrm/services/api.dart';
import 'package:bkrm/services/info/inventoryInfo/purchasedSheetInfo.dart';
import 'package:bkrm/services/info/managementInfo/storeInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/supplierInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';
import 'package:flutter/cupertino.dart';

class ImportGoodItem {
  ItemInfo item;
  late int _amount;
  int? _importedPrice;
  ImportGoodItem(this.item) {
    this._amount = 1;
    this._importedPrice = item.purchasePrice;
  }

  int get amount => _amount;

  set importedPrice(int value) {
    if (value < 0) {
      _importedPrice = 0;
    } else {
      _importedPrice = value;
    }
  }

  set amount(int value) {
    if (value < 0) {
      _amount = 0;
    } else {
      _amount = value;
    }
  }

  updateItem() async {
    item =
        (await BkrmService().searchItemInBranch(priceId: [item.priceId])).first;
  }

  @override
  bool operator ==(covariant ImportGoodItem other) {
    if (this.item.priceId == other.item.priceId) {
      return true;
    } else {
      return false;
    }
  }

  int get importedPrice => _importedPrice!;

  Map<String, dynamic> exportMap() {
    return {
      "item_id": item.itemId,
      "quantity": amount,
      "purchase_price": importedPrice
    };
  }
}

class ImportGoodService {
  List<ImportGoodItem?> _importItems = [];
  bool get empty => _importItems.isEmpty;
  SupplierInfo? _supplier;
  Store? store;
  int? _discount;
  String? _deliverName;
  StreamController _importGoodServiceStreamController =
      StreamController.broadcast();
  Stream get importGoodServiceStream =>
      _importGoodServiceStreamController.stream;

  String? get deliverName => _deliverName;

  set deliverName(String? value) {
    if (value == "") {
      _deliverName = null;
    } else {
      _deliverName = value;
    }
  }

  int get totalPrice {
    int totalPrice = 0;
    _importItems.forEach((element) {
      totalPrice +=
          (element!._importedPrice == null ? 0 : element._importedPrice)! *
              element._amount;
    });
    return totalPrice;
  }

  SupplierInfo? get supplier => _supplier;

  set supplier(SupplierInfo? value) {
    _supplier = value;
  }

  int? get discount => _discount;

  set discount(int? value) {
    _discount = value;
  }

  void updateInfo() {
    _importGoodServiceStreamController.sink.add({
      "importItems": _importItems,
      "supplier": _supplier,
      "discount": _discount,
      "deliverName": deliverName,
      "totalPrice": totalPrice
    });
  }

  Future<bool> addToImport(ItemInfo item) async {
    if (_importItems
        .where((element) => element!.item.priceId == item.priceId)
        .isNotEmpty) {
      return false;
    }
    _importItems.add(ImportGoodItem(item));
    updateInfo();
    return true;
  }

  bool replaceItem(ImportGoodItem? item) {
    if (!_importItems.contains(item)) {
      return false;
    } else {
      int idx = _importItems.indexWhere((element) => element == item);
      if (idx < 0) {
        return false;
      }
      _importItems[idx] = item;
      updateInfo();
      return true;
    }
  }

  bool deleteItem(int? priceId) {
    if (_importItems
        .where((element) => element!.item.priceId == priceId)
        .isEmpty) {
      return false;
    }
    _importItems.removeWhere((element) => element!.item.priceId == priceId);
    updateInfo();
    return true;
  }

  void dispose() {
    _importGoodServiceStreamController.close();
  }

  Map<String, dynamic> exportInvoice() {
    List<Map<String, dynamic>> listExportMapItem = [];
    _importItems.forEach((element) {
      listExportMapItem.add(element!.exportMap());
    });
    return {
      "purchased_sheet": {
        "supplier_id": this.supplier != null ? this.supplier!.id : null,
        "total_purchase_price": this.totalPrice,
        "discount": this.discount == null ? 0 : this.discount,
        "deliver_name": deliverName
      },
      "purchased_items": listExportMapItem
    };
  }

  Future<Map<String, dynamic>> sendInvoice() async {
    Map<String, dynamic> returnStatus = await ApiService().createImportInvoice(
        exportInvoice(),
        BkrmService().currentUser!.storeId,
        BkrmService().currentUser!.branchId);
    if (returnStatus["state"] == "success") {
      Map<String, dynamic> purchasedSheetInfoMap =
          returnStatus["purchased_sheet"][0];
      PurchasedSheetInfo purchasedSheetInfo = PurchasedSheetInfo(
          purchasedSheetId:
              purchasedSheetInfoMap["purchased_sheet_id"].toString(),
          branchId: purchasedSheetInfoMap["branch_id"].toString(),
          purchaserId: purchasedSheetInfoMap["purchaser_id"].toString(),
          purchaserName: purchasedSheetInfoMap["purchaser_name"].toString(),
          supplierId: purchasedSheetInfoMap["supplier_id"].toString(),
          supplierName: purchasedSheetInfoMap["supplier_name"].toString(),
          supplierPhone: purchasedSheetInfoMap["supplier_phone"].toString(),
          totalPurchasePrice:
              purchasedSheetInfoMap["total_purchase_price"].toString(),
          discount: purchasedSheetInfoMap["discount"].toString(),
          deliverName: purchasedSheetInfoMap["deliver_name"].toString(),
          deliveryDate: purchasedSheetInfoMap["delivery_datetime"].toString());
      List<PurchasedItem> listPurchasedItems = [];
      debugPrint(returnStatus.toString());
      for(Map<String,dynamic> purchasedItem in returnStatus["purchased_sheet_items"]){
        PurchasedItem tempItem = PurchasedItem(purchasedSheetId: purchasedItem["purchased_sheet_id"].toString(), purchasedItemId: purchasedItem["purchased_item_id"].toString(),
            purchasePrice: purchasedItem["purchase_price"].toString(), quantity: purchasedItem["quantity"].toString(),
            itemId: purchasedItem["item_id"].toString(), name: purchasedItem["name"].toString(), imageUrl: purchasedItem["image_url"].toString());
        listPurchasedItems.add(tempItem);
      }
      DetailPurchasedSheetInfo detailPurchasedSheetInfo = DetailPurchasedSheetInfo(purchasedSheetInfo, listPurchasedItems);
      return {"state": MsgInfoCode.actionSuccess,"detailPurchasedSheet":detailPurchasedSheetInfo};
    } else {
      debugPrint(returnStatus["errors"].toString());
      return {"state": MsgInfoCode.actionFail};
    }
  }

  clearImportService() {
    _importItems.clear();
    supplier = null;
    store = null;
    discount = null;
    deliverName = null;
    updateInfo();
  }
}
