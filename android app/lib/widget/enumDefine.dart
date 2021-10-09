import 'package:bkrm/services/info/inventoryInfo/purchasedSheetInfo.dart';
import 'package:bkrm/widget/productItem.dart';

///
/// Use for pos page and inventory page
///

enum SortCriteria {
  nameAscending,
  nameDescending,
  sellerNameAscending,
  sellerNameDescending,
  priceAscending,
  priceDescending,
  amountStockAscending,
  amountStockDescending,
  dateCreateAscending,
  dateCreateDescending,
  discountAscending,
  discountDescending,
  idAscending,
  idDescending,
  dateUpdateAscending,
  dateUpdateDescending
}
enum Criteria { name, price, amount, date, discount, id,sellerName }

class SortFunction {
  static Function nameAscendingItem = (ProductItem a, ProductItem b) {
    return a.rawDataItem.itemName!
        .toLowerCase()
        .compareTo(b.rawDataItem.itemName!.toLowerCase());
  };
  static Function nameDescendingItem = (ProductItem b, ProductItem a) {
    return a.rawDataItem.itemName!
        .toLowerCase()
        .compareTo(b.rawDataItem.itemName!.toLowerCase());
  };
  static Function priceAscendingItem = (ProductItem a, ProductItem b) {
    return a.rawDataItem.sellPrice.compareTo(b.rawDataItem.sellPrice);
  };
  static Function priceDescendingItem = (ProductItem b, ProductItem a) {
    return a.rawDataItem.sellPrice.compareTo(b.rawDataItem.sellPrice);
  };
  static Function amountAscendingItem = (ProductItem a, ProductItem b) {
    return a.rawDataItem.quantity.compareTo(b.rawDataItem.quantity);
  };
  static Function amountDescendingItem = (ProductItem b, ProductItem a) {
    return a.rawDataItem.quantity.compareTo(b.rawDataItem.quantity);
  };
  static Function dateCreatedAscendingItem = (ProductItem a, ProductItem b) {
    return a.rawDataItem.itemId.compareTo(b.rawDataItem.itemId);
  };
  static Function dateCreatedDescendingItem = (ProductItem b, ProductItem a) {
    return a.rawDataItem.itemId.compareTo(b.rawDataItem.itemId);
  };
  static Function nameAscendingImportInvoice = (PurchasedSheetInfo a, PurchasedSheetInfo b) {
    return a.supplierName!
        .toLowerCase()
        .compareTo(b.supplierName!.toLowerCase());
  };
  static Function nameDescendingImportInvoice = (PurchasedSheetInfo b, PurchasedSheetInfo a) {
    return a.supplierName!
        .toLowerCase()
        .compareTo(b.supplierName!.toLowerCase());
  };
  static Function priceAscendingImportInvoice = (PurchasedSheetInfo a, PurchasedSheetInfo b) {
    return a.totalPurchasePrice!.compareTo(b.totalPurchasePrice!);
  };
  static Function priceDescendingImportInvoice = (PurchasedSheetInfo b, PurchasedSheetInfo a) {
    return a.totalPurchasePrice!.compareTo(b.totalPurchasePrice!);
  };
  static Function dateDeliveryAscendingImportInvoice = (PurchasedSheetInfo a, PurchasedSheetInfo b) {
    return a.deliveryDate!.compareTo(b.deliveryDate!);
  };
  static Function dateDeliveryDescendingImportInvoice = (PurchasedSheetInfo b, PurchasedSheetInfo a) {
    return a.deliveryDate!.compareTo(b.deliveryDate!);
  };
  static Function discountAscendingImportInvoice = (PurchasedSheetInfo a, PurchasedSheetInfo b) {
    return a.discount!.compareTo(b.discount!);
  };
  static Function discountDescendingImportInvoice = (PurchasedSheetInfo b, PurchasedSheetInfo a) {
    return a.discount!.compareTo(b.discount!);
  };

}
