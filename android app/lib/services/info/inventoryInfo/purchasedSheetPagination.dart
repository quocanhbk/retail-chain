import 'package:bkrm/services/info/inventoryInfo/purchasedSheetInfo.dart';

class PurchasedSheetPagination{
  int currentPage;
  int lastPage;
  int purchasedSheetsPerPage;
  List<PurchasedSheetInfo> purchasedSheets;

  PurchasedSheetPagination(
      this.currentPage, this.lastPage, this.purchasedSheetsPerPage, this.purchasedSheets);
}