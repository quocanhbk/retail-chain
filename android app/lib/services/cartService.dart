import 'dart:async';

import 'package:bkrm/services/api.dart';
import 'package:bkrm/services/info/sellingInfo/customerInfo.dart';
import 'package:bkrm/services/info/invoice/invoiceReceivedWhenCreated.dart';
import 'package:bkrm/services/services.dart';
import 'package:flutter/cupertino.dart';
import 'package:intl/intl.dart';

import 'package:bkrm/services/info/invoice/sendInvoiceInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';

class CartItem {
  ItemInfo item;
  int _amount;
  double discountPercent = -1;
  CartItem(this.item, this._amount, {double? discountPercent}){
    this.discountPercent=discountPercent??-1;
  }
  int get discountPrice {
    return item.sellPrice;
  }
  bool valid = true;
  StreamController _cartItemController = StreamController.broadcast();
  Stream get cartItemStream => _cartItemController.stream;
  int get priceId => item.priceId;
  int get amount => _amount;
  set amount(int value) {
    if (value >= 1 && value <= item.quantity) {
      _amount = value;
    }
  }

  @override
  bool operator ==(covariant CartItem other) {
    if (this.priceId == other.priceId) {
      return true;
    } else {
      return false;
    }
  }

  @override
  int get hashCode => super.hashCode;

  void dispose() {
    _cartItemController.close();
  }

  @override
  String toString() {
    return "Item :" +
        priceId.toString() +
        " ,amount =" +
        _amount.toString() +
        " ,discountPrice = " +
        discountPrice.toString() +
        " ,discounrPercent =" +
        discountPercent.toString();
  }
}

class CartService {
  List<CartItem> cartItems = [];
  bool valid = true;
  bool get empty => cartItems.isEmpty;
  StreamController _cartServiceController = StreamController.broadcast();
  Stream get cartServiceStream => _cartServiceController.stream;
  StreamController _cartServicePriceController = StreamController.broadcast();
  double totalPrice = 0;
  double totalDiscountPrice = 0;
  double totalDiscount = 0;
  int? customerPoint = 0;
  String status = "success";
  CustomerInfo? _customer;

  bool usedCustomerPoint = false;

  CustomerInfo? get customer => _customer;

  set customer(CustomerInfo? value) {
    if (value == null) {
      this.customerPoint = 0;
      this.usedCustomerPoint=false;
      calculateAllValueInCart();
    }
    _customer = value;
  }

  checkCartValid() async {
    List<int?> listPriceId = [];
    cartItems.forEach((element) {
      listPriceId.add(element.priceId);
    });
    valid = true;
    List<int> itemIds = [];
    cartItems.forEach((cartItem) async {
      itemIds.add(cartItem.item.itemId);
      cartItem.valid = true;
    });
    List<ItemInfo>? listItem = await BkrmService().getItems(itemId: itemIds);
    listItem.forEach((element) {
      for (var cartItem in cartItems) {
        if (element.itemId == cartItem.item.itemId) {
          if (element.quantity < cartItem._amount) {
            cartItem.valid = false;
            valid = false;
            break;
          }
          if(element.priceId!=cartItem.item.priceId){
            cartItem.item=element;
          }
        }
      }
    });
    calculateAllValueInCart();
    BkrmService().requestCart();
    return valid;
  }

  bool calculateAllValueInCart() {
    totalDiscount = 0;
    totalDiscountPrice = 0;
    totalPrice = 0;
    cartItems.forEach((element) {
      totalPrice += element.item.sellPrice * element._amount;
      totalDiscountPrice +=
          element.discountPrice * element._amount - this.customerPoint!;
      totalDiscount +=
          (element.item.sellPrice - element.discountPrice) * element._amount +
              customerPoint!;
    });
    return true;
  }

  bool useCustomerPoint(bool? use) {
    if (customer != null) {
      if (use!) {
        this.customerPoint = this.customer!.customerPoint;
        usedCustomerPoint=true;
        calculateAllValueInCart();
      } else {
        this.customerPoint = 0;
        usedCustomerPoint=false;
        calculateAllValueInCart();
      }
    }
    return true;
  }

  addCartItem(ItemInfo item, int amount) {
    CartItem newCartItem = CartItem(item, amount);
    if (cartItems.contains(newCartItem)) {
      cartItems[cartItems.indexOf(newCartItem)]._amount += newCartItem._amount;
      calculateAllValueInCart();
      BkrmService().requestCart();
      checkCartValid();
      return true;
    } else {
      cartItems.add(CartItem(item, amount));
      calculateAllValueInCart();
      BkrmService().requestCart();
      checkCartValid();
      return true;
    }
  }

  getCartItem(int? id) {
    for (CartItem? cartItem in cartItems) {
      if (cartItem!.item.itemId == id) {
        return cartItem;
      }
    }
    return null;
  }

  clearCart() {
    cartItems.clear();
    totalPrice = 0;
    totalDiscount = 0;
    totalDiscountPrice = 0;
    // totalProfitValue=0;
    valid = true;
    customer = null;
    BkrmService().requestCart();
    return true;
  }

  removeCartItem(CartItem? item) {
    for (CartItem? element in cartItems) {
      if (item == element) {
        cartItems.remove(element);
        BkrmService().requestCart();
        checkCartValid();
        break;
      }
    }
    BkrmService().requestCart();
  }

  modifyCartItem(CartItem? newItem) {
    if(newItem!=null){
      if (cartItems.contains(newItem)) {
        debugPrint(newItem.toString());
        cartItems[cartItems.indexOf(newItem)] = newItem;
        checkCartValid();
        calculateAllValueInCart();
        BkrmService().requestCart();
        return true;
      } else {
        return false;
      }
    }
  }

  Future<Map<String, dynamic>> sendInvoice(
      double change, double customerPay) async {
    ApiService api = ApiService();
    BkrmService bkrmService = BkrmService();
    SendInvoiceInfo invoice = SendInvoiceInfo(
        customer == null ? null : this.customer!.id,
        customer == null ? null : this.customer!.phoneNumber,
        this.totalDiscountPrice.toInt(),
        this.totalDiscount.toInt(),
        this.status,
        this.customerPoint,
        this.cartItems);
    debugPrint(invoice.exportInvoice().toString());
    Map<String, dynamic> result = await api.createInvoice(
        invoice.exportInvoice(),
        bkrmService.currentUser!.storeId,
        bkrmService.currentUser!.branchId);
    if (!BkrmService().networkAvailable) {
      InvoiceReceivedWhenCreated createdInvoice = InvoiceReceivedWhenCreated(
          invoiceId: (-1).toString(),
          totalSellPrice: invoice.totalSellValue.toString(),
          discount: invoice.discount.toString(),
          createdDatetime: DateFormat("yyyy-MM-dd HH:mm:ss").format(DateTime.now()),
          status: status,
          customerId: invoice.customerId.toString(),
          customerName: this._customer != null
              ? (this._customer!.name == null
                  ? "Khách hàng lẻ"
                  : this._customer!.name!)
              : "Khách hàng lẻ",
          sellerId: BkrmService().currentUser!.userId.toString(),
          sellerName: BkrmService().currentUser!.name);
      debugPrint("Done create invoice");
      List<Item> listItem = [];
      for(CartItem item in cartItems){
        Item createdInvoiceItem = Item(
            sellPrice: item.item.sellPrice.toString(),
            quantity: item._amount.toString(),
            name: item.item.itemName!);
        listItem.add(createdInvoiceItem);
        List<ItemInfo> items = BkrmService().storedItemInfo.where((element) => element.itemId==item.item.itemId).toList();
        if(items.isNotEmpty){
          items.first.quantity=items.first.quantity-item._amount;
        }
      }
      debugPrint("Done create invoice item");
      return {
        "state": MsgInfoCode.actionSuccess,
        "invoice": DetailInvoiceInfo(createdInvoice, listItem)
      };
    }
    debugPrint("return invoice form server");
    debugPrint(result["created_invoice"].toString());
    debugPrint(result["created_invoice_items"].toString());
    if (result["state"] == "success") {
      InvoiceReceivedWhenCreated? createdInvoice;
      for (Map<String, dynamic> createdInvoiceMap
          in result["created_invoice"]) {
        createdInvoice = InvoiceReceivedWhenCreated(
            invoiceId: createdInvoiceMap["invoice_id"].toString(),
            totalSellPrice: createdInvoiceMap["total_sell_price"].toString(),
            discount: createdInvoiceMap["discount"].toString(),
            createdDatetime: createdInvoiceMap["created_datetime"].toString(),
            status: createdInvoiceMap["status"].toString(),
            customerId: createdInvoiceMap["customer_id"].toString(),
            customerName: createdInvoiceMap["customer_name"].toString(),
            sellerId: createdInvoiceMap["seller_id"].toString(),
            sellerName: createdInvoiceMap["seller_name"].toString());
        break;
      }
      List<Item> createdItems = [];
      for (Map<String, dynamic> createdInvoiceItemMap
          in result["created_invoice_items"]) {
        Item createdInvoiceItem = Item(
            sellPrice: createdInvoiceItemMap["sell_price"].toString(),
            quantity: createdInvoiceItemMap["quantity"].toString(),
            name: createdInvoiceItemMap["name"].toString());
        createdItems.add(createdInvoiceItem);
      }
      return {
        "state": MsgInfoCode.actionSuccess,
        "invoice": DetailInvoiceInfo(createdInvoice, createdItems)
      };
    } else {
      return {"state": MsgInfoCode.actionSuccess, "errors": result["errors"]};
    }
  }

  void dispose() {
    _cartServiceController.close();
    _cartServicePriceController.close();
  }
}
