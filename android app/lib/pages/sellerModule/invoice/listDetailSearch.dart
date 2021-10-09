import 'package:bkrm/pages/sellerModule/invoice/listInvoicePage.dart';
import 'package:bkrm/services/info/invoice/invoiceReceivedWhenGet.dart';
import 'package:bkrm/services/services.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';

class ListDetailSearch extends StatefulWidget {
  int? searchId;
  String searchQuery="";
  int? totalMoneyFrom;
  int? totalMoneyTo;
  DateTime? dateTimeFrom;
  DateTime? dateTimeTo;

  ListDetailSearch(
      {int? searchId,
        String? searchQuery,
      int? totalMoneyFrom,
      int? totalMoneyTo,
      DateTime? dateTimeFrom,
      DateTime? dateTimeTo}) {
    this.searchId = searchId;
    this.searchQuery=searchQuery==null?"":searchQuery;
    this.totalMoneyFrom = totalMoneyFrom;
    this.totalMoneyTo = totalMoneyTo;
    this.dateTimeFrom = dateTimeFrom;
    this.dateTimeTo = dateTimeTo;
  }

  @override
  _ListDetailSearchState createState() => _ListDetailSearchState();
}

class _ListDetailSearchState extends State<ListDetailSearch> {
  SortListCriteria? sortListCriteria;
  List<InvoiceReceivedWhenGet> invoices = [];
  late ListInvoices listInvoice;
  BkrmService bkrmService = BkrmService();
  @override
  void initState() {
    super.initState();
    listInvoice=ListInvoices(
      filterFrom: widget.dateTimeFrom,
      filterTo: widget.dateTimeTo,
      searchId: widget.searchId,
      searchQuery: widget.searchQuery,
      totalMoneyFrom: widget.totalMoneyFrom,
      totalMoneyTo: widget.totalMoneyTo,
    );
    sortListCriteria = SortListCriteria(listInvoice);
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
                if (sortListCriteria != null) {
                  showDialog(
                      context: context,
                      builder: (context) {
                        return sortListCriteria!;
                      });
                }
              }),
        ],
      ),
      body: WillPopScope(
        onWillPop: ()async{
          debugPrint("Need refresh list detail search page");
          debugPrint(listInvoice.needRefresh.toString());
          Navigator.pop(context,listInvoice.needRefresh);
          return listInvoice.needRefresh;
        },
        child: Container(
          child: Padding(
            padding: const EdgeInsets.all(8.0),
            child: listInvoice,
          ),
        ),
      ),
    );
  }
}
