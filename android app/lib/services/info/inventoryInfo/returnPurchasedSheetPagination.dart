import 'package:bkrm/services/info/inventoryInfo/returnPurchasedSheetInfo.dart';

class ReturnPurchasedSheetPagination{
  int currentPage;
  int lastPage;
  int returnPurchasedSheetsPerPage;
  List<ReturnPurchasedSheetInfo> returnPurchasedSheets;

  ReturnPurchasedSheetPagination(
      this.currentPage, this.lastPage, this.returnPurchasedSheetsPerPage, this.returnPurchasedSheets);
}