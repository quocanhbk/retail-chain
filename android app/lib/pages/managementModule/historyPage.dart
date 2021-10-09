import 'package:bkrm/pages/inventoryModule/item/detailQuantityCheckingPage.dart';
import 'package:bkrm/pages/inventoryModule/purchasedSheet/purchasedSheetDetailPage.dart';
import 'package:bkrm/pages/inventoryModule/returnPurchasedSheet/returnPurchasedDetailPage.dart';
import 'package:bkrm/pages/sellerModule/invoice/invoiceDetailPage.dart';
import 'package:bkrm/pages/sellerModule/refund/refundDetailPage.dart';
import 'package:bkrm/services/info/hrInfo/employeeInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/purchasedSheetInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/purchasedSheetPagination.dart';
import 'package:bkrm/services/info/inventoryInfo/quantityHistory.dart';
import 'package:bkrm/services/info/inventoryInfo/returnPurchasedSheetInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/returnPurchasedSheetPagination.dart';
import 'package:bkrm/services/info/invoice/invoiceReceivedWhenGet.dart';
import 'package:bkrm/services/info/managementInfo/historyInfo.dart';
import 'package:bkrm/services/info/sellingInfo/invoicePagination.dart';
import 'package:bkrm/services/info/sellingInfo/refundInfo.dart';
import 'package:bkrm/services/info/sellingInfo/refundPagination.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/menuWidget.dart';
import 'package:flutter/material.dart';
import 'package:flutter_datetime_picker/flutter_datetime_picker.dart';
import 'package:intl/intl.dart';

class HistoryPage extends StatefulWidget {
  @override
  _HistoryPageState createState() => _HistoryPageState();
}

class _HistoryPageState extends State<HistoryPage> {
  GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey();
  GlobalKey<RefreshIndicatorState> _refreshKey = GlobalKey();
  List<HistoryInfo>? listHistory;
  DateTime? curDate;

  EmployeeInfo? employee;
  DateTime? fromDate;
  DateTime? toDate;
  String? type;

  @override
  void initState() {
    super.initState();
    getHistory().then((value) {
      setState(() {});
    });
  }

  Future<List<HistoryInfo>?> getHistory() async {
    List<HistoryInfo>? returnHistory = await BkrmService().getHistory(
        userId: employee?.userId,
        fromDate: fromDate,
        toDate: toDate,
        type: type);
    curDate=null;
    listHistory = returnHistory;
    return listHistory;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      key: _scaffoldKey,
      appBar: AppBar(
        title: Text("Lịch sử hoạt động"),
        actions: [
          IconButton(
              onPressed: () {
                _scaffoldKey.currentState?.openEndDrawer();
              },
              icon: Icon(Icons.filter_list))
        ],
      ),
      drawer: ExpansionDrawer(this.context),
      endDrawer: FilterDrawer(
        onConfirmFilter: (EmployeeInfo? employee, DateTime? fromDate,
            DateTime? toDate, String type) {
          setState(() {
            this.employee = employee;
            this.fromDate = fromDate;
            this.toDate = toDate;
            this.type = type == "all" ? null : type;
          });
          _refreshKey.currentState?.show();
        },
        employee: employee,
        fromDate: fromDate,
        toDate: toDate,
        type: type ?? "all",
      ),
      body: listHistory == null
          ? Container(
              child: Center(
                child: Text(
                  "Đang tải dữ liệu",
                  style: TextStyle(
                      fontWeight: FontWeight.w300,
                      color: Colors.grey,
                      fontSize: 16),
                ),
              ),
            )
          : RefreshIndicator(
              key: _refreshKey,
              onRefresh: () async {
                await getHistory();
                setState(() {

                });
              },
              child: ListView.separated(
                  itemBuilder: (context, index) {
                    if (index == 0) {
                      return Container();
                    }
                    if (index > listHistory!.length) {
                      return Container();
                    }
                    HistoryInfo history = listHistory![index - 1];
                    late RichText content;
                    late Function onTapFunction;
                    switch (history.type) {
                      case "invoices":
                        content = RichText(
                            text: TextSpan(children: [
                          TextSpan(
                              text: history.userName,
                              style: TextStyle(fontWeight: FontWeight.bold,color: Colors.black)),
                          TextSpan(text: " đã thanh toán một hóa đơn",
                              style: TextStyle(color: Colors.black))
                        ]));
                        onTapFunction = (HistoryInfo historyInfo) async {
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
                          InvoicePagination? invoicePagination =
                              await BkrmService().getInvoices(
                                  page: 1,
                                  orderBy: "created_datetime",
                                  order: "desc",
                                  invoiceId: historyInfo.id);
                          if (invoicePagination != null &&
                              invoicePagination.invoices.isNotEmpty) {
                            DetailInvoiceInfo? detailInvoice =
                                await BkrmService().getDetailInvoice(
                                    invoicePagination.invoices.first);
                            if (detailInvoice != null) {
                              RefundPagination? refundPagination =
                                  await BkrmService().getRefundSheets(
                                      page: 1,
                                      orderBy: "created_datetime",
                                      order: "desc",
                                      invoiceId: invoicePagination
                                          .invoices.first.invoiceId);
                              if (refundPagination != null) {
                                Navigator.pop(context);
                                Navigator.push(context,
                                    MaterialPageRoute(builder: (context) {
                                  return InvoiceDetailPage(
                                      detailInvoice, refundPagination.refunds);
                                }));
                                return;
                              }
                            }
                          }
                          Navigator.pop(context);
                        };
                        break;
                      case "purchased_sheets":
                        content = RichText(
                            text: TextSpan(children: [
                          TextSpan(
                              text: history.userName,
                              style: TextStyle(fontWeight: FontWeight.bold,color: Colors.black)),
                          TextSpan(text: " đã thực hiện một đơn nhập hàng",
                              style: TextStyle(color: Colors.black))
                        ]));
                        onTapFunction = (HistoryInfo historyInfo) async {
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
                          PurchasedSheetPagination? purchasedSheetPagination =
                              await BkrmService().getImportInvoices(
                                  page: 1,
                                  orderBy: "created_datetime",
                                  order: "desc",
                                  purchasedSheetId: historyInfo.id);
                          if (purchasedSheetPagination != null &&
                              purchasedSheetPagination
                                  .purchasedSheets.isNotEmpty) {
                            DetailPurchasedSheetInfo? detailPurchasedSheet =
                                await BkrmService().getDetailPurchasedSheet(
                                    purchasedSheetPagination
                                        .purchasedSheets.first);
                            if (detailPurchasedSheet != null) {
                              ReturnPurchasedSheetPagination?
                                  returnPurchasedSheetPagination =
                                  await BkrmService().getReturnPurchasedSheet(
                                      page: 1,
                                      orderBy: "created_datetime",
                                      order: "desc",
                                      purchasedSheetId: purchasedSheetPagination
                                          .purchasedSheets
                                          .first
                                          .purchasedSheetId);
                              if (returnPurchasedSheetPagination != null) {
                                Navigator.pop(context);
                                Navigator.push(context,
                                    MaterialPageRoute(builder: (context) {
                                  return PurchasedSheetDetailPage(
                                      detailPurchasedSheet,
                                      returnPurchasedSheetPagination
                                          .returnPurchasedSheets);
                                }));
                                return;
                              }
                            }
                          }
                          Navigator.pop(context);
                        };
                        break;
                      case "refund_sheets":
                        content = RichText(
                            text: TextSpan(children: [
                          TextSpan(
                              text: history.userName,
                              style: TextStyle(fontWeight: FontWeight.bold,color: Colors.black)),
                          TextSpan(
                              text:
                                  " đã thực hiện trả hàng cho một hóa đơn bán hàng",
                              style: TextStyle(color: Colors.black))
                        ]));
                        onTapFunction = (HistoryInfo historyInfo) async {
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
                          RefundPagination? refundPagination =
                              await BkrmService().getRefundSheets(
                                  page: 1,
                                  orderBy: "created_datetime",
                                  order: "desc",
                                  refundSheetId: historyInfo.id);
                          if (refundPagination != null &&
                              refundPagination.refunds.isNotEmpty) {
                            DetailRefundSheet? detailRefund =
                                await BkrmService().getDetailRefundSheet(
                                    refundPagination.refunds.first);
                            if (detailRefund != null) {
                              Navigator.pop(context);
                              Navigator.push(context,
                                  MaterialPageRoute(builder: (context) {
                                return RefundDetailPage(detailRefund);
                              }));
                              return;
                            }
                          }
                          Navigator.pop(context);
                        };
                        break;
                      case "return_purchased_sheets":
                        content = RichText(
                            text: TextSpan(children: [
                          TextSpan(
                              text: history.userName,
                              style: TextStyle(fontWeight: FontWeight.bold,color: Colors.black)),
                          TextSpan(
                              text:
                                  " đã thực hiện trả hàng cho một đơn nhập hàng",
                              style: TextStyle(color: Colors.black))
                        ]));
                        onTapFunction = (HistoryInfo historyInfo) async {
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
                          ReturnPurchasedSheetPagination?
                              returnPurchasedPagination = await BkrmService()
                                  .getReturnPurchasedSheet(
                                      page: 1,
                                      orderBy: "created_datetime",
                                      order: "desc",
                                      returnPurchasedSheetId: historyInfo.id);
                          if (returnPurchasedPagination != null &&
                              returnPurchasedPagination
                                  .returnPurchasedSheets.isNotEmpty) {
                            DetailReturnPurchasedSheetInfo?
                                detailReturnPurchased = await BkrmService()
                                    .getDetailReturnPurchasedSheet(
                                        returnPurchasedPagination
                                            .returnPurchasedSheets.first);
                            if (detailReturnPurchased != null) {
                              Navigator.pop(context);
                              Navigator.push(context,
                                  MaterialPageRoute(builder: (context) {
                                return ReturnPurchasedSheetDetail(
                                    detailReturnPurchased);
                              }));
                              return;
                            }
                          }
                          Navigator.pop(context);
                        };
                        break;
                      case "quantity_checking_sheets":
                        content = RichText(
                            text: TextSpan(children: [
                          TextSpan(
                              text: history.userName,
                              style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  color: Colors.black)),
                          TextSpan(
                              text: " đã kiểm kê hàng",
                              style: TextStyle(color: Colors.black))
                        ]));
                        onTapFunction = (HistoryInfo historyInfo) async {
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
                          QuantityHistory? quantityHistory = await BkrmService()
                              .getQuantityHistory(
                                  quantityCheckingSheetId: historyInfo.id);
                          if (quantityHistory != null &&
                              quantityHistory.quantityHistory.isNotEmpty) {
                            List<ItemInfo> itemInfo = await BkrmService()
                                .getItems(itemId: [
                              quantityHistory.quantityHistory.first.itemId
                            ]);
                            if (itemInfo.isNotEmpty) {
                              Navigator.pop(context);
                              Navigator.push(context,
                                  MaterialPageRoute(builder: (context) {
                                return QuantityCheckingDetailPage(
                                    quantityHistory.quantityHistory.first,
                                    itemInfo.first);
                              }));
                              return;
                            }
                          }
                          Navigator.pop(context);
                        };
                        break;
                      default:
                        content = RichText(
                            text: TextSpan(children: [
                          TextSpan(
                              text: "",
                              style: TextStyle(fontWeight: FontWeight.bold)),
                          TextSpan(text: "")
                        ]));
                        onTapFunction = () async {};
                        break;
                    }
                    return Container(
                      padding: EdgeInsets.all(10.0),
                      child: InkWell(
                        onTap: () async {
                          await onTapFunction(history);
                        },
                        child: Row(
                          children: [
                            Expanded(
                                flex: 8,
                                child: Container(
                                    alignment: Alignment.centerLeft,
                                    child: content)),
                            Expanded(
                                flex: 2,
                                child: Container(
                                    alignment: Alignment.centerRight,
                                    child: Text(DateFormat("HH:mm:ss")
                                        .format(history.createdDateTime))))
                          ],
                        ),
                      ),
                    );
                  },
                  separatorBuilder: (context, index) {

                    if (index == 0) {
                      curDate = listHistory![index].createdDateTime;
                      return Container(
                        decoration: BoxDecoration(
                            border: Border.symmetric(horizontal: BorderSide())),
                        padding: EdgeInsets.all(10.0),
                        child: Center(
                            child: Text(
                          DateFormat("dd-MM-yyyy")
                              .format(listHistory![index].createdDateTime),
                          style: TextStyle(
                              fontSize: 14, fontWeight: FontWeight.bold),
                          textAlign: TextAlign.center,
                        )),
                      );
                    }
                    if (index > listHistory!.length) {
                      return Container();
                    }
                    if (DateFormat("dd-MM-yyyy")
                            .format(listHistory![index - 1].createdDateTime) !=
                        DateFormat("dd-MM-yyyy").format(curDate!)) {
                      curDate = listHistory![index - 1].createdDateTime;
                      return Container(
                        decoration: BoxDecoration(
                            border: Border.symmetric(horizontal: BorderSide())),
                        padding: EdgeInsets.all(10.0),
                        child: Center(
                            child: Text(
                          DateFormat("dd-MM-yyyy")
                              .format(listHistory![index - 1].createdDateTime),
                          style: TextStyle(
                              fontSize: 14, fontWeight: FontWeight.bold),
                          textAlign: TextAlign.center,
                        )),
                      );
                    } else {
                      return Divider();
                    }
                  },
                  itemCount: listHistory!.length + 1),
            ),
    );
  }
}

class FilterDrawer extends StatefulWidget {
  EmployeeInfo? employee;
  DateTime? fromDate;
  DateTime? toDate;
  String type = "all";

  Function(EmployeeInfo?, DateTime?, DateTime?, String type) onConfirmFilter;

  FilterDrawer(
      {required this.onConfirmFilter,
      this.employee,
      this.fromDate,
      this.toDate,
      required this.type});

  @override
  _FilterDrawerState createState() => _FilterDrawerState();
}

class _FilterDrawerState extends State<FilterDrawer> {
  @override
  Widget build(BuildContext context) {
    return Drawer(
      child: Container(
        padding: EdgeInsets.all(8.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            SizedBox(
              height: 30,
            ),
            Center(
              child: Text(
                "Lọc",
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 20,
                ),
                textAlign: TextAlign.center,
              ),
            ),
            Divider(),
            SizedBox(
              height: 20,
            ),
            Row(
              children: [
                Expanded(flex: 2, child: Text("Nhân viên: ")),
                Expanded(
                    flex: 4,
                    child: InkWell(
                        onTap: () async {
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
                          List<EmployeeInfo> listEmployee =
                              await BkrmService().getEmployee();
                          Navigator.pop(context);
                          if (listEmployee.isEmpty) {
                            showDialog(
                                context: context,
                                builder: (context) {
                                  return AlertDialog(
                                    title: Text("Chọn nhận viên"),
                                    content: Container(
                                      height: 50,
                                      child: Center(
                                        child: Text(
                                          "Không có nhân viên",
                                          style: TextStyle(
                                              fontWeight: FontWeight.w300,
                                              color: Colors.grey,
                                              fontSize: 14),
                                        ),
                                      ),
                                    ),
                                  );
                                });
                            return;
                          } else {
                            showDialog(
                                context: context,
                                builder: (context) {
                                  return AlertDialog(
                                    title: Text("Chọn nhận viên"),
                                    content: Container(
                                      width: MediaQuery.of(context).size.width *
                                          3 /
                                          4,
                                      child: ListView.separated(
                                          itemBuilder: (context, index) {
                                            return ListTile(
                                              title: Text(
                                                  listEmployee[index].name),
                                              subtitle:
                                                  listEmployee[index].phone !=
                                                              null &&
                                                          listEmployee[index]
                                                                  .phone !=
                                                              "null"
                                                      ? Text(listEmployee[index]
                                                          .phone!)
                                                      : null,
                                              onTap: () {
                                                this.setState(() {
                                                  widget.employee =
                                                      listEmployee[index];
                                                });
                                                Navigator.pop(context);
                                              },
                                            );
                                          },
                                          separatorBuilder: (context, index) {
                                            return Divider();
                                          },
                                          itemCount: listEmployee.length),
                                    ),
                                    actions: [
                                      TextButton(
                                          onPressed: () {
                                            Navigator.pop(context);
                                          },
                                          child: Text("Hủy"))
                                    ],
                                  );
                                });
                          }
                        },
                        child: IgnorePointer(
                            child: TextField(
                          controller: TextEditingController(
                              text: widget.employee != null
                                  ? widget.employee!.name
                                  : ""),
                        )))),
                Expanded(
                    flex: 1,
                    child: IconButton(
                      icon: Icon(Icons.close),
                      onPressed: () {
                        widget.employee = null;
                        setState(() {});
                      },
                    )),
              ],
            ),
            SizedBox(
              height: 20,
            ),
            Center(
              child: Text(
                "Thời gian",
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
              ),
            ),
            Row(
              children: [
                Expanded(flex: 2, child: Text("Từ :")),
                Expanded(
                  flex: 4,
                  child: InkWell(
                      onTap: () {
                        DatePicker.showDatePicker(context,
                            maxTime: widget.toDate,
                            locale: LocaleType.vi, onConfirm: (DateTime? date) {
                          setState(() {
                            widget.fromDate = date;
                          });
                        });
                      },
                      child: IgnorePointer(
                          child: TextField(
                        controller: TextEditingController(
                            text: widget.fromDate != null
                                ? DateFormat("dd-MM-yyyy")
                                    .format(widget.fromDate!)
                                : null),
                      ))),
                ),
                Expanded(
                    flex: 1,
                    child: IconButton(
                      icon: Icon(Icons.close),
                      onPressed: () {
                        setState(() {
                          widget.fromDate = null;
                        });
                      },
                    ))
              ],
            ),
            Row(
              children: [
                Expanded(flex: 2, child: Text("Đến :")),
                Expanded(
                  flex: 4,
                  child: InkWell(
                      onTap: () {
                        DatePicker.showDatePicker(context,
                            minTime: widget.fromDate,
                            locale: LocaleType.vi, onConfirm: (DateTime? date) {
                          setState(() {
                            widget.toDate = date;
                          });
                        });
                      },
                      child: IgnorePointer(
                          child: TextField(
                        controller: TextEditingController(
                            text: widget.toDate != null
                                ? DateFormat("dd-MM-yyyy")
                                    .format(widget.toDate!)
                                : null),
                      ))),
                ),
                Expanded(
                    flex: 1,
                    child: IconButton(
                      icon: Icon(Icons.close),
                      onPressed: () {
                        setState(() {
                          widget.toDate = null;
                        });
                      },
                    ))
              ],
            ),
            SizedBox(
              height: 20,
            ),
            Center(
              child: Text(
                "Loại lịch sử",
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
              ),
            ),
            DropdownButton(
                value: widget.type,
                onChanged: (String? value) {
                  setState(() {
                    debugPrint(value);
                    if (value != null) {
                      widget.type = value;
                    }
                  });
                },
                items: <DropdownMenuItem<String>>[
                  DropdownMenuItem(
                    child: Text("Tất cả"),
                    value: "all",
                  ),
                  DropdownMenuItem(
                    child: Text("Hóa đơn"),
                    value: "invoices",
                  ),
                  DropdownMenuItem(
                    child: Text("Đơn nhâp hàng"),
                    value: "purchased_sheets",
                  ),
                  DropdownMenuItem(
                    child: Text("Đơn trả hàng"),
                    value: "refund_sheets",
                  ),
                  DropdownMenuItem(
                    child: Text("Đơn trả hàng nhập"),
                    value: "return_purchased_sheets",
                  ),
                  DropdownMenuItem(
                    child: Text("Đơn trả hàng nhập"),
                    value: "quantity_checking_sheets",
                  ),
                ]),
            Expanded(child: Container()),
            Row(
              children: [
                Expanded(
                    child: TextButton(
                  onPressed: () {
                    Navigator.pop(context);
                  },
                  child: Text("Hủy"),
                )),
                Expanded(
                    child: TextButton(
                  onPressed: () {
                    widget.onConfirmFilter(widget.employee, widget.fromDate,
                        widget.toDate, widget.type);
                    Navigator.pop(context);
                  },
                  child: Text("Xác nhận"),
                )),
              ],
            )
          ],
        ),
      ),
    );
  }
}
