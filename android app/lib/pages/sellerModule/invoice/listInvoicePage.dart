import 'package:bkrm/pages/sellerModule/invoice/invoiceDetailPage.dart';
import 'package:bkrm/services/info/invoice/invoiceReceivedWhenGet.dart';
import 'package:bkrm/services/info/sellingInfo/invoicePagination.dart';
import 'package:bkrm/services/info/sellingInfo/refundInfo.dart';
import 'package:bkrm/services/info/sellingInfo/refundPagination.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/enumDefine.dart';
import 'package:bkrm/widget/menuWidget.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter_barcode_scanner/flutter_barcode_scanner.dart';
import 'package:flutter_datetime_picker/flutter_datetime_picker.dart';
import 'package:infinite_scroll_pagination/infinite_scroll_pagination.dart';
import 'package:intl/intl.dart';
import 'package:page_transition/page_transition.dart';
import 'package:permission_handler/permission_handler.dart';

import 'package:bkrm/pages/Nav2App.dart';
import 'detailSearchInvoicePage.dart';

enum SortCriteria {
  customerNameAscending,
  customerNameDescending,
  sellerNameAscending,
  sellerNameDescending,
  totalPriceAscending,
  totalPriceDescending,
  discountAscending,
  discountDescending,
  dateSellAscending,
  dateSellDescending
}

class SortFunction {
  static Function customerAscendingInvoice =
      (InvoiceReceivedWhenGet a, InvoiceReceivedWhenGet b) {
    return a.customerName!
        .toLowerCase()
        .compareTo(b.customerName!.toLowerCase());
  };
  static Function customerDescendingInvoice =
      (InvoiceReceivedWhenGet b, InvoiceReceivedWhenGet a) {
    return a.customerName!
        .toLowerCase()
        .compareTo(b.customerName!.toLowerCase());
  };
  static Function totalPriceAscendingInvoice =
      (InvoiceReceivedWhenGet a, InvoiceReceivedWhenGet b) {
    return a.totalSellPrice!.compareTo(b.totalSellPrice!);
  };
  static Function totalPriceDescendingInvoice =
      (InvoiceReceivedWhenGet b, InvoiceReceivedWhenGet a) {
    return a.totalSellPrice!.compareTo(b.totalSellPrice!);
  };
  static Function dateSellAscendingInvoice =
      (InvoiceReceivedWhenGet a, InvoiceReceivedWhenGet b) {
    return a.createdDatetime!.compareTo(b.createdDatetime!);
  };
  static Function dateSellDescendingInvoice =
      (InvoiceReceivedWhenGet b, InvoiceReceivedWhenGet a) {
    return a.createdDatetime!.compareTo(b.createdDatetime!);
  };
  static Function sellerNameAscendingInvoice =
      (InvoiceReceivedWhenGet a, InvoiceReceivedWhenGet b) {
    return a.sellerName!.toLowerCase().compareTo(b.sellerName!.toLowerCase());
  };
  static Function sellerNameDescendingInvoice =
      (InvoiceReceivedWhenGet b, InvoiceReceivedWhenGet a) {
    return a.sellerName!.toLowerCase().compareTo(b.sellerName!.toLowerCase());
  };
}

class DatePickerDialog extends StatefulWidget {
  Function(DateTime?, DateTime?)? onDonePick;
  DateTime? from = DateTime.now();
  DateTime? to = DateTime.now();
  DatePickerDialog(DateTime? from, DateTime? to,
      {Function(DateTime?, DateTime?)? onDonePick}) {
    this.onDonePick = onDonePick;
    this.from = from;
    this.to = to;
  }

  @override
  _DatePickerDialogState createState() => _DatePickerDialogState();
}

class _DatePickerDialogState extends State<DatePickerDialog> {
  @override
  Widget build(BuildContext context) {
    return Container(
      child: AlertDialog(
        title: Text("Ch???n kho???ng th???i gian ????? l???c"),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Expanded(
                  flex: 1,
                  child: Text(
                    "T??? : ",
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                ),
                Expanded(
                  flex: 3,
                  child: OutlineButton(
                      borderSide: BorderSide(width: 1.0),
                      color: Colors.grey,
                      onPressed: () {
                        DatePicker.showDatePicker(context,
                            currentTime: widget.from,
                            maxTime: DateTime.now(), onConfirm: (date) {
                          widget.from = date;
                          setState(() {});
                        }, locale: LocaleType.vi);
                      },
                      child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          children: [
                            Text(
                              DateFormat("dd-MM-yyyy").format(widget.from!),
                              style: TextStyle(
                                  fontSize: 16, fontWeight: FontWeight.bold),
                            ),
                            Icon(Icons.calendar_today)
                          ])),
                )
              ],
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Expanded(
                  flex: 1,
                  child: Text(
                    "?????n: ",
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                ),
                Expanded(
                  flex: 3,
                  child: OutlineButton(
                      borderSide: BorderSide(width: 1.0),
                      color: Colors.grey,
                      onPressed: () {
                        DatePicker.showDatePicker(context,
                            currentTime: widget.to,
                            maxTime: DateTime.now(), onConfirm: (date) {
                          widget.to = date;
                          setState(() {});
                        }, locale: LocaleType.vi);
                      },
                      child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          children: [
                            Text(
                              DateFormat("dd-MM-yyyy").format(widget.to!),
                              style: TextStyle(
                                  fontSize: 16, fontWeight: FontWeight.bold),
                            ),
                            Icon(Icons.calendar_today)
                          ])),
                )
              ],
            )
          ],
        ),
        actions: [
          TextButton(
              onPressed: () {
                if (widget.onDonePick != null) {
                  widget.onDonePick!(null, null);
                }
                Navigator.pop(context);
              },
              child: Text(
                "D???ng l???c",
                style: TextStyle(fontSize: 14),
              )),
          TextButton(
              onPressed: () {
                Navigator.pop(context);
              },
              child: Text(
                "Hu???",
                style: TextStyle(fontSize: 14),
              )),
          TextButton(
              onPressed: () {
                if (widget.onDonePick != null) {
                  widget.onDonePick!(widget.from, widget.to);
                }
                Navigator.pop(context);
              },
              child: Text(
                "L???c",
                style: TextStyle(fontSize: 14),
              )),
        ],
      ),
    );
  }
}

class SortListCriteria extends StatefulWidget {
  final ListInvoices? listInvoice;
  SortCriteria? sortCriteria = SortCriteria.dateSellDescending;
  Criteria? selectedCriteria = Criteria.date;
  SortListCriteria(this.listInvoice);

  @override
  _SortListCriteriaState createState() => _SortListCriteriaState();
}

class _SortListCriteriaState extends State<SortListCriteria> {
  Widget radioButtonGroup = Container();
  @override
  void initState() {
    super.initState();
    buildRadioButton();
  }

  void buildRadioButton() {
    switch (widget.selectedCriteria) {
      case Criteria.name:
        radioButtonGroup = Column(
          children: [
            Row(
              children: [
                Text("Theo th??? t??? th???p ?????n cao "),
                Radio(
                    value: SortCriteria.customerNameAscending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listInvoice!
                          .sortInvoice(orderBy: "customer_name", order: "asc");
                      Navigator.pop(context);
                    })
              ],
            ),
            Row(
              children: [
                Text("Theo th??? t??? cao ?????n th???p "),
                Radio(
                    value: SortCriteria.customerNameDescending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listInvoice!
                          .sortInvoice(orderBy: "customer_name", order: "desc");
                      Navigator.pop(context);
                    })
              ],
            )
          ],
        );
        break;
      case Criteria.sellerName:
        radioButtonGroup = Column(
          children: [
            Row(
              children: [
                Text("Theo th??? t??? th???p ?????n cao "),
                Radio(
                    value: SortCriteria.sellerNameAscending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listInvoice!
                          .sortInvoice(orderBy: "seller_name", order: "asc");
                      Navigator.pop(context);
                    })
              ],
            ),
            Row(
              children: [
                Text("Theo th??? t??? cao ?????n th???p "),
                Radio(
                    value: SortCriteria.sellerNameDescending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listInvoice!
                          .sortInvoice(orderBy: "seller_name", order: "desc");
                      Navigator.pop(context);
                    })
              ],
            )
          ],
        );
        break;
      case Criteria.price:
        radioButtonGroup = Column(
          children: [
            Row(
              children: [
                Text("Theo th??? t??? th???p ?????n cao "),
                Radio(
                    value: SortCriteria.totalPriceAscending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listInvoice!.sortInvoice(
                          orderBy: "total_sell_price", order: "asc");
                      Navigator.pop(context);
                    })
              ],
            ),
            Row(
              children: [
                Text("Theo th??? t??? cao ?????n th???p "),
                Radio(
                    value: SortCriteria.totalPriceDescending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listInvoice!.sortInvoice(
                          orderBy: "total_sell_price", order: "desc");
                      Navigator.pop(context);
                    })
              ],
            )
          ],
        );
        break;
      case Criteria.date:
        radioButtonGroup = Column(
          children: [
            Row(
              children: [
                Text("Theo th??? t??? th???p ?????n cao "),
                Radio(
                    value: SortCriteria.dateSellAscending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listInvoice!.sortInvoice(
                          orderBy: "created_datetime", order: "asc");
                      Navigator.pop(context);
                    })
              ],
            ),
            Row(
              children: [
                Text("Theo th??? t??? cao ?????n th???p "),
                Radio(
                    value: SortCriteria.dateSellDescending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listInvoice!.sortInvoice(
                          orderBy: "created_datetime", order: "desc");
                      Navigator.pop(context);
                    })
              ],
            )
          ],
        );
        break;
    }
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text("S???p x???p"),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(children: [
            DropdownButton(
                value: widget.selectedCriteria,
                items: <DropdownMenuItem>[
                  DropdownMenuItem(
                    child: Text("T??n kh??ch h??ng"),
                    value: Criteria.name,
                  ),
                  DropdownMenuItem(
                    child: Text("T???ng ti???n h??a ????n"),
                    value: Criteria.price,
                  ),
                  DropdownMenuItem(
                    child: Text("T??n ng?????i b??n"),
                    value: Criteria.sellerName,
                  ),
                  DropdownMenuItem(
                    child: Text("Ng??y b??n"),
                    value: Criteria.date,
                  ),
                ],
                onChanged: (dynamic value) {
                  setState(() {
                    widget.selectedCriteria = value;
                    buildRadioButton();
                  });
                }),
          ]),
          radioButtonGroup
        ],
      ),
    );
  }
}

class ListInvoices extends StatefulWidget {
  String? searchQuery;
  late _ListInvoicesState _state;
  DateTime? filterFrom;
  DateTime? filterTo;
  int? searchId;
  int? totalMoneyFrom;
  int? totalMoneyTo;
  bool needRefresh = false;
  GlobalKey<RefreshIndicatorState>? refreshKey;
  ListInvoices(
      {DateTime? filterFrom,
      DateTime? filterTo,
      int? searchId,
      int? totalMoneyFrom,
      int? totalMoneyTo,
      String? searchQuery,
      GlobalKey<RefreshIndicatorState>? refreshKey,
      bool? needRefresh}) {
    this.filterTo = filterTo;
    this.filterFrom = filterFrom;
    this.searchId = searchId;
    this.totalMoneyFrom = totalMoneyFrom;
    this.totalMoneyTo = totalMoneyTo;
    this.searchQuery = searchQuery == null ? "" : searchQuery;
    this.refreshKey = refreshKey;
    if (needRefresh != null) {
      this.needRefresh = needRefresh;
    }
  }

  void searchList(String query) {
    if (query == "") {
      this.searchQuery = null;
      refresh();
      return;
    }
    this.searchQuery = query;
    refresh();
  }

  void setDateFilter(DateTime? from, DateTime? to) {
    filterTo = null;
    filterFrom = null;
    if (from == null) {
      this.filterFrom = null;
    } else {
      this.filterFrom = DateTime.parse(DateFormat("yyyy-MM-dd").format(from));
    }
    if (to == null) {
      this.filterTo = null;
    } else {
      this.filterTo = DateTime.parse(DateFormat("yyyy-MM-dd").format(to))
          .add(Duration(days: 1))
          .subtract(Duration(seconds: 1));
    }
    if (filterTo == null && filterFrom != null) {
      filterTo = DateTime.now();
    }
    if (filterTo != null && filterFrom == null) {
      filterFrom = DateTime.fromMicrosecondsSinceEpoch(0);
    }
    refresh();
  }

  void sortInvoice({required String orderBy, required String order}) {
    _state.sort(orderBy: orderBy, order: order);
    refresh();
  }

  void refresh() {
    _state._pagingController.refresh();
  }

  @override
  _ListInvoicesState createState() {
    _state = _ListInvoicesState();
    return _state;
  }
}

class _ListInvoicesState extends State<ListInvoices> {
  String orderBy = "created_datetime";
  String order = "desc";
  final PagingController<int, InvoiceReceivedWhenGet> _pagingController =
      PagingController(firstPageKey: 1);
  NumberFormat formatter = NumberFormat();

  sort({required String orderBy, required String order}) {
    this.orderBy = orderBy;
    this.order = order;
    _pagingController.refresh();
  }

  @override
  void initState() {
    _pagingController.addPageRequestListener((pageKey) {
      _fetchPage(pageKey);
    });
    super.initState();
  }

  Future<void> _fetchPage(int pageKey) async {
    try {
      debugPrint("Pagekey " + pageKey.toString());
      debugPrint("Fetch page");
      InvoicePagination? itemPage = await BkrmService().getInvoices(
          page: pageKey,
          orderBy: orderBy,
          order: order,
          invoiceId: widget.searchId,
          keyword: widget.searchQuery,
          totalMoneyFrom: widget.totalMoneyFrom,
          totalMoneyTo: widget.totalMoneyTo,
          createdFrom: widget.filterFrom,
          createdTo: widget.filterTo);
      if (itemPage == null) {
        _pagingController.appendLastPage([]);
        return;
      }
      if (itemPage.currentPage == itemPage.lastPage) {
        debugPrint("last page");
        _pagingController.appendLastPage(itemPage.invoices);
      } else {
        final nextPageKey = pageKey + 1;
        _pagingController.appendPage(itemPage.invoices, nextPageKey);
      }
    } catch (error) {
      _pagingController.error = error;
    }
  }

  void filterList() {}

  @override
  Widget build(BuildContext context) {
    filterList();
    return Column(mainAxisSize: MainAxisSize.min, children: [
      Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Expanded(
              flex: 2,
              child: Padding(
                padding: const EdgeInsets.all(3.0),
                child: Text(
                  "#",
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              )),
          Expanded(
              flex: 5,
              child: Padding(
                padding: const EdgeInsets.all(3.0),
                child: Text(
                  "Kh??ch h??ng",
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              )),
          Expanded(
              flex: 6,
              child: Padding(
                padding: const EdgeInsets.all(3.0),
                child: Text(
                  "T???ng ti???n ",
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              )),
          Expanded(
              flex: 4,
              child: Padding(
                padding: const EdgeInsets.all(3.0),
                child: Text(
                  "Ng?????i b??n",
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              )),
          Expanded(
              flex: 5,
              child: Padding(
                padding: const EdgeInsets.all(3.0),
                child: Text(
                  "Ng??y b??n",
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ))
        ],
      ),
      Divider(
        thickness: 2.0,
      ),
      Expanded(
        child: RefreshIndicator(
          onRefresh: () async {
            _pagingController.refresh();
          },
          child: PagedListView.separated(
            builderDelegate: PagedChildBuilderDelegate(
              itemBuilder: (BuildContext context,
                  InvoiceReceivedWhenGet invoice, int index) {
                return InkWell(
                  onTap: () async {
                    showDialog(
                        context: context,
                        builder: (BuildContext context) {
                          return AlertDialog(
                            content: Container(
                              height: 50,
                              child: Center(
                                child: CircularProgressIndicator(),
                              ),
                            ),
                          );
                        });
                    DetailInvoiceInfo? detailInvoice =
                        await BkrmService().getDetailInvoice(invoice);
                    RefundPagination? refundPage = await BkrmService()
                        .getRefundSheets(
                            page: 1,
                            orderBy: "created_datetime",
                            order: "asc",
                            invoiceId: invoice.invoiceId);
                    Navigator.pop(context);
                    if (detailInvoice != null) {
                      Navigator.push(context,
                          PageTransition(child: InvoiceDetailPage(detailInvoice,
                              refundPage != null ? refundPage.refunds : []), type: pageTransitionType)).then((value) {
                        if (value) {
                          _pagingController.refresh();
                        }
                      });
                    } else {
                      showDialog(
                          context: context,
                          builder: (BuildContext context) {
                            return AlertDialog(
                              title: Text("???? c?? l???i x???y ra!"),
                              actions: [
                                TextButton(
                                    onPressed: () {
                                      Navigator.pop(context);
                                    },
                                    child: Text("????ng"))
                              ],
                            );
                          });
                    }
                  },
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Expanded(
                          flex: 2,
                          child: Padding(
                            padding: const EdgeInsets.all(3.0),
                            child: Text(invoice.invoiceId.toString()),
                          )),
                      Expanded(
                          flex: 5,
                          child: Padding(
                            padding: const EdgeInsets.all(3.0),
                            child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(invoice.customerName == null
                                      ? "Kh??ch h??ng l???"
                                      : invoice.customerName!),
                                  invoice.customerPhone != null
                                      ? Text(
                                          invoice.customerPhone == "null"
                                              ? ""
                                              : invoice.customerPhone!,
                                          style: TextStyle(
                                              color: Colors.grey,
                                              fontWeight: FontWeight.w300,
                                              fontSize: 13),
                                        )
                                      : Container()
                                ]),
                          )),
                      Expanded(
                          flex: 6,
                          child: Padding(
                            padding: const EdgeInsets.all(3.0),
                            child:
                                Text(formatter.format(invoice.totalSellPrice)),
                          )),
                      Expanded(
                          flex: 4,
                          child: Padding(
                            padding: const EdgeInsets.all(3.0),
                            child: Text(invoice.sellerName!),
                          )),
                      Expanded(
                          flex: 5,
                          child: Padding(
                            padding: const EdgeInsets.all(3.0),
                            child: Text(DateFormat("dd-MM-yyyy HH:mm:ss")
                                .format(invoice.createdDatetime!)),
                          ))
                    ],
                  ),
                );
              },
              noItemsFoundIndicatorBuilder: (context) {
                return Container(
                  height: MediaQuery.of(context).size.height / 2,
                  child: Center(
                    child: Text(
                      "Kh??ng c?? h??a ????n",
                      style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w300,
                          color: Colors.grey),
                    ),
                  ),
                );
              },
              firstPageErrorIndicatorBuilder: (context) {
                return Container(
                  height: MediaQuery.of(context).size.height / 2,
                  child: Center(
                    child: Text(
                      "???? c?? l???i x???y ra",
                      style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w300,
                          color: Colors.red),
                    ),
                  ),
                );
              },
            ),
            pagingController: _pagingController,
            separatorBuilder: (BuildContext context, int index) {
              return Divider();
            },
          ),
        ),
      ),
    ]);
  }
}

class InvoicePage extends StatefulWidget {
  @override
  _InvoicePageState createState() => _InvoicePageState();
}

class _InvoicePageState extends State<InvoicePage> {
  GlobalKey<RefreshIndicatorState> _refreshKey = GlobalKey();
  late SortListCriteria sortListCriteria;
  List<InvoiceReceivedWhenGet>? invoices;
  late ListInvoices listInvoices;
  BkrmService bkrmService = BkrmService();
  TextEditingController controller = TextEditingController();
  @override
  void initState() {
    super.initState();
    listInvoices = ListInvoices(
      refreshKey: _refreshKey,
    );
    this.sortListCriteria = SortListCriteria(listInvoices);
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        FocusScope.of(context).requestFocus(FocusNode());
      },
      child: Scaffold(
        resizeToAvoidBottomInset: false,
        appBar: AppBar(
          title: Text("H??a ????n"),
          actions: [
            IconButton(
                icon: Icon(Icons.qr_code),
                onPressed: () async {
                  var status = await Permission.camera.status;
                  if (status.isPermanentlyDenied ||
                      status.isRestricted ||
                      status.isDenied) {
                    await Permission.camera.request();
                  }
                  var scanResult = await FlutterBarcodeScanner.scanBarcode(
                      "#ffffff",
                      "H???y",
                      true,
                      ScanMode.DEFAULT);
                  if(scanResult=="-1"){
                    return;
                  }
                  showDialog(
                      context: context,
                      builder: (context) {
                        return AlertDialog(
                          content: Container(
                            height: 50,
                            child: Center(
                              child: CircularProgressIndicator(),
                            ),
                          ),
                        );
                      });
                  InvoicePagination? invoicePage = await BkrmService()
                      .getInvoices(
                          page: 1, orderBy: "created_datetime", order: "asc",invoiceId: int.tryParse(scanResult)??-1);
                  if (invoicePage == null) {
                    Navigator.pop(context);
                    if (scanResult != null) {
                      showDialog(
                          context: context,
                          builder: (context) {
                            return AlertDialog(
                              title: Text("Th??ng b??o"),
                              content: Container(
                                height: 50,
                                child: Center(
                                  child: Text(
                                    "Kh??ng t??m th???y h??a ????n #" + scanResult,
                                    textAlign: TextAlign.center,
                                    style: (TextStyle(fontSize: 18)),
                                  ),
                                ),
                              ),
                              actions: [
                                FlatButton(
                                    onPressed: () {
                                      Navigator.pop(context);
                                    },
                                    child: Text("????ng"))
                              ],
                            );
                          });
                    }
                    return;
                  }
                  DetailInvoiceInfo? detailInvoice = await BkrmService()
                      .getDetailInvoice(invoicePage.invoices.first);
                  RefundPagination? refundSheets = await BkrmService()
                      .getRefundSheets(
                          page: 1,
                          orderBy: "created_datetime",
                          order: "asc",
                          invoiceId: invoicePage.invoices.first.invoiceId);
                  Navigator.pop(context);
                  Navigator.push(context, PageTransition(child: InvoiceDetailPage(detailInvoice,
                      refundSheets != null ? refundSheets.refunds : []), type: pageTransitionType)).then((value) {
                    if (value) {
                      if (_refreshKey.currentState != null) {
                        _refreshKey.currentState!.show();
                      } else {
                        listInvoices.refresh();
                      }
                    }
                  });
                  return;
                }),
            IconButton(
                icon: Icon(Icons.date_range),
                onPressed: () {
                  showDialog(
                      context: context,
                      builder: (context) {
                        return DatePickerDialog(
                          listInvoices.filterFrom != null
                              ? listInvoices.filterFrom
                              : DateTime.now(),
                          listInvoices.filterTo != null
                              ? listInvoices.filterTo
                              : DateTime.now(),
                          onDonePick: (from, to) {
                            listInvoices.setDateFilter(from, to);
                          },
                        );
                      });
                }),
            IconButton(
                icon: Icon(Icons.sort),
                onPressed: () {
                  showDialog(
                      context: context,
                      builder: (context) {
                        return sortListCriteria;
                      });
                })
          ],
        ),
        drawer: ExpansionDrawer(context),
        body: Container(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(children: [
                Expanded(
                  child: Padding(
                    padding: const EdgeInsets.all(8.0),
                    child: TextField(
                      controller: controller,
                      onSubmitted: (value) {
                        listInvoices.searchList(value);
                      },
                      decoration: InputDecoration(
                          labelText: "T??m ki???m",
                          hintText: "Nh???p t??n ho???c s??? ??i???n tho???i",
                          prefixIcon: Icon(Icons.search),
                          suffixIcon: IconButton(
                            onPressed: () {
                              controller.clear();
                              listInvoices.searchList("");
                            },
                            icon: Icon(Icons.clear),
                          ),
                          border: OutlineInputBorder(
                              borderRadius:
                                  BorderRadius.all(Radius.circular(25.0)))),
                    ),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.fromLTRB(0, 0, 4.0, 0),
                  child: FlatButton(
                    color: Colors.lightBlueAccent,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10.0)),
                    onPressed: () {
                      Navigator.push(context,
                          PageTransition(child: DetailSearchInvoicePage(),type:pageTransitionType)).then((value) {
                        if (value != null) {
                          if (value) {
                            if (_refreshKey.currentState != null) {
                              _refreshKey.currentState!.show();
                            } else {
                              setState(() {
                                listInvoices.refresh();
                              });
                            }
                          }
                        }
                      });
                    },
                    child: Text("T??m ki???m \nchi ti???t"),
                  ),
                )
              ]),
              Expanded(
                child: listInvoices,
              )
            ],
          ),
        ),
      ),
    );
  }
}
