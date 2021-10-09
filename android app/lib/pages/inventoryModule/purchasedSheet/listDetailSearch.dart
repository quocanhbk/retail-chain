import 'package:bkrm/services/services.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';

import 'package:bkrm/pages/inventoryModule/purchasedSheet//purchasedSheetPage.dart';

class ListDetailSearch extends StatefulWidget {
  int? searchId;
  String searchNameSupplier = "";
  int? totalMoneyFrom;
  int? totalMoneyTo;
  DateTime? dateTimeFrom;
  DateTime? dateTimeTo;

  ListDetailSearch(
      {int? searchId,
      String? searchNameSupplier,
      int? totalMoneyFrom,
      int? totalMoneyTo,
      DateTime? dateTimeFrom,
      DateTime? dateTimeTo}) {
    this.searchId = searchId;
    this.searchNameSupplier =
        searchNameSupplier == null ? "" : searchNameSupplier;
    this.totalMoneyFrom = totalMoneyFrom;
    this.totalMoneyTo = totalMoneyTo;
    this.dateTimeFrom = dateTimeFrom;
    this.dateTimeTo = dateTimeTo;
  }

  @override
  _ListDetailSearchState createState() => _ListDetailSearchState();
}

class _ListDetailSearchState extends State<ListDetailSearch> {
  late SortListCriteria sortListCriteria;
  late ListPurchasedSheet listPurchasedSheets;
  BkrmService bkrmService = BkrmService();
  @override
  void initState() {
    super.initState();
    listPurchasedSheets = ListPurchasedSheet(
      filterFrom: widget.dateTimeFrom,
      filterTo: widget.dateTimeTo,
      searchId: widget.searchId,
      searchQuery: widget.searchNameSupplier,
      totalMoneyFrom: widget.totalMoneyFrom,
      totalMoneyTo: widget.totalMoneyTo,
    );
    sortListCriteria = SortListCriteria(listPurchasedSheets);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text("Kết quả tìm kiếm chi tiết"),
        actions: [
          IconButton(
              icon: Icon(Icons.sort),
              onPressed: () {
                  showDialog(
                      context: context,
                      builder: (context) {
                        return sortListCriteria;
                      });
                }
              ),
        ],
      ),
      body: Container(
        child: Padding(
          padding: const EdgeInsets.all(8.0),
          child: listPurchasedSheets),
        ),
      );
  }
}
