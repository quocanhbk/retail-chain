import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';

class ItemPage{
  int currentPage;
  int lastPage;
  int itemPerPage;
  List<ItemInfo> items;

  ItemPage(this.currentPage, this.lastPage, this.itemPerPage, this.items);
}