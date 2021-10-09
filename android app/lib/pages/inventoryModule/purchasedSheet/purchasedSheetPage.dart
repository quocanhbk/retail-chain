import 'package:bkrm/services/info/inventoryInfo/purchasedSheetInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/purchasedSheetPagination.dart';
import 'package:bkrm/services/info/inventoryInfo/returnPurchasedSheetInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/returnPurchasedSheetPagination.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/enumDefine.dart';
import 'package:bkrm/widget/menuWidget.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter_datetime_picker/flutter_datetime_picker.dart';
import 'package:infinite_scroll_pagination/infinite_scroll_pagination.dart';
import 'package:intl/intl.dart';

import 'package:bkrm/pages/inventoryModule/purchasedSheet/detailSearchPurchasedSheetPage.dart';
import 'package:bkrm/pages/inventoryModule/purchasedSheet/purchasedSheetDetailPage.dart';
import 'package:page_transition/page_transition.dart';

import 'package:bkrm/pages/Nav2App.dart';

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
        title: Text("Chọn thời gian"),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Expanded(
                  flex: 1,
                  child: Text(
                    "Từ : ",
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
                    "Đến: ",
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
                "Dừng lọc",
                style: TextStyle(fontSize: 16),
              )),
          TextButton(
              onPressed: () {
                Navigator.pop(context);
              },
              child: Text(
                "Huỷ",
                style: TextStyle(fontSize: 16),
              )),
          TextButton(
              onPressed: () {
                if (widget.onDonePick != null) {
                  widget.onDonePick!(widget.from, widget.to);
                }
                Navigator.pop(context);
              },
              child: Text(
                "Lọc",
                style: TextStyle(fontSize: 16),
              )),
        ],
      ),
    );
  }
}

class SortListCriteria extends StatefulWidget {
  final ListPurchasedSheet? listPurchasedSheet;
  SortCriteria? sortCriteria = SortCriteria.dateCreateDescending;
  Criteria? selectedCriteria = Criteria.date;
  SortListCriteria(this.listPurchasedSheet);

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
                Text("Theo thứ tự thấp đến cao "),
                Radio(
                    value: SortCriteria.nameAscending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listPurchasedSheet!.sortImportInvoice(orderBy: "supplier_name",order: "asc");
                      Navigator.pop(context);
                    })
              ],
            ),
            Row(
              children: [
                Text("Theo thứ tự cao đến thấp "),
                Radio(
                    value: SortCriteria.nameDescending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listPurchasedSheet!.sortImportInvoice(orderBy: "supplier_name",order: "desc");
                      Navigator.pop(context);
                    })
              ],
            )
          ],
        );
        break;
      case Criteria.discount:
        radioButtonGroup = Column(
          children: [
            Row(
              children: [
                Text("Theo thứ tự thấp đến cao "),
                Radio(
                    value: SortCriteria.discountAscending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listPurchasedSheet!.sortImportInvoice(orderBy: "discount",order: "asc");
                      Navigator.pop(context);
                    })
              ],
            ),
            Row(
              children: [
                Text("Theo thứ tự cao đến thấp "),
                Radio(
                    value: SortCriteria.discountDescending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listPurchasedSheet!.sortImportInvoice(orderBy: "discount",order: "desc");
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
                Text("Theo thứ tự thấp đến cao "),
                Radio(
                    value: SortCriteria.priceAscending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listPurchasedSheet!.sortImportInvoice(orderBy: "total_purchase_price",order: "asc");
                      Navigator.pop(context);
                    })
              ],
            ),
            Row(
              children: [
                Text("Theo thứ tự cao đến thấp "),
                Radio(
                    value: SortCriteria.priceDescending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listPurchasedSheet!.sortImportInvoice(orderBy: "total_purchase_price",order: "desc");
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
                Text("Theo thứ tự thấp đến cao "),
                Radio(
                    value: SortCriteria.dateUpdateAscending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listPurchasedSheet!.sortImportInvoice(orderBy: "created_datetime",order: "asc");
                      Navigator.pop(context);
                    })
              ],
            ),
            Row(
              children: [
                Text("Theo thứ tự cao đến thấp "),
                Radio(
                    value: SortCriteria.dateUpdateDescending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listPurchasedSheet!.sortImportInvoice(orderBy: "created_datetime",order: "desc");
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
      title: Text("Sắp xếp"),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(children: [
            DropdownButton(
                value: widget.selectedCriteria,
                items: <DropdownMenuItem>[
                  DropdownMenuItem(
                    child: Text("Nhà cung cấp"),
                    value: Criteria.name,
                  ),
                  DropdownMenuItem(
                    child: Text("Tổng tiền hàng"),
                    value: Criteria.price,
                  ),
                  DropdownMenuItem(
                    child: Text("Giảm giá"),
                    value: Criteria.discount,
                  ),
                  DropdownMenuItem(
                    child: Text("Ngày nhập"),
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

class ListPurchasedSheet extends StatefulWidget {

  String orderBy="created_datetime";
  String order="desc";
  String searchQuery = "";
  late _ListPurchasedSheetState _state;
  DateTime? filterFrom;
  DateTime? filterTo;
  int? searchId;
  int? totalMoneyFrom;
  int? totalMoneyTo;

  ListPurchasedSheet(
      {DateTime? filterFrom,
      DateTime? filterTo,
      int? searchId,
      int? totalMoneyFrom,
      int? totalMoneyTo,
      String? searchQuery}) {
    this.filterTo = filterTo;
    this.filterFrom = filterFrom;
    this.searchId = searchId;
    this.totalMoneyFrom = totalMoneyFrom;
    this.totalMoneyTo = totalMoneyTo;
    this.searchQuery = searchQuery == null ? "" : searchQuery;
  }

  void searchList(String query) {
    this.searchQuery = query;
    _state._pagingController.refresh();
  }

  void setDateFilter(DateTime? from, DateTime? to) {
    if(from==null){
      this.filterFrom=null;
    }else{
      this.filterFrom = DateTime.parse(DateFormat("yyyy-MM-dd").format(from));
    }
    if(to==null){
      this.filterTo=null;
    }else{
      this.filterTo = DateTime.parse(DateFormat("yyyy-MM-dd").format(to))
          .add(Duration(days: 1))
          .subtract(Duration(seconds: 1));
    }
    _state._pagingController.refresh();
  }

  void sortImportInvoice({required String orderBy,required String order}) {
    this.orderBy=orderBy;
    this.order=order;
    _state._pagingController.refresh();
  }
  void refeesh(){
    _state._pagingController.refresh();
  }
  @override
  _ListPurchasedSheetState createState() {
    _state = _ListPurchasedSheetState();
    return _state;
  }
}

class _ListPurchasedSheetState extends State<ListPurchasedSheet> {
  NumberFormat formatter = NumberFormat();
  final PagingController<int, PurchasedSheetInfo> _pagingController =
  PagingController(firstPageKey: 1);


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
      PurchasedSheetPagination? itemPage = await BkrmService().getImportInvoices(
          page: pageKey,
          orderBy: widget.orderBy,
          order: widget.order,
          purchasedSheetId: widget.searchId,
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
        _pagingController.appendLastPage(itemPage.purchasedSheets);
      } else {
        final nextPageKey = pageKey + 1;
        _pagingController.appendPage(itemPage.purchasedSheets, nextPageKey);
      }
    } catch (error) {
      _pagingController.error = error;
    }
  }

  @override
  Widget build(BuildContext context) {
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
                  "Nhà cung cấp",
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              )),
          Expanded(
              flex: 6,
              child: Padding(
                padding: const EdgeInsets.all(3.0),
                child: Text(
                  "Tổng tiền hàng",
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              )),
          Expanded(
              flex: 4,
              child: Padding(
                padding: const EdgeInsets.all(3.0),
                child: Text(
                  "Giảm giá",
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              )),
          Expanded(
              flex: 5,
              child: Padding(
                padding: const EdgeInsets.all(3.0),
                child: Text(
                  "Ngày nhập",
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ))
        ],
      ),
      Divider(
        thickness: 2.0,
      ),
      Expanded(
        child: PagedListView(
          pagingController: _pagingController,
            shrinkWrap: true,
          builderDelegate: PagedChildBuilderDelegate(
            itemBuilder: (context,PurchasedSheetInfo purchasedSheet ,int index){
              return InkWell(
                onTap: () async {
                  showDialog(
                      context: context,
                      builder: (context) {
                        return AlertDialog(
                          content: Container(
                              height: 30,
                              child: Center(
                                child: CircularProgressIndicator(),
                              )),
                        );
                      });
                  DetailPurchasedSheetInfo? detailImport = await BkrmService()
                      .getDetailPurchasedSheet(
                      purchasedSheet)
                      .catchError((error) {
                    Navigator.pop(context);
                    showDialog(
                        context: context,
                        builder: (context) {
                          return AlertDialog(
                            title: Text(
                                "Đã có lỗi xảy ra. Vui lòng thử lại hoặc kiểm tra kết nối mạng!"),
                            actions: [
                              FlatButton(
                                  onPressed: () {
                                    Navigator.pop(context);
                                  },
                                  child: Text("Đóng"))
                            ],
                          );
                        });
                  });
                  ReturnPurchasedSheetPagination? returnPurchasedSheet = await BkrmService().getReturnPurchasedSheet(page: 1, orderBy: "created_datetime", order: "desc",purchasedSheetId: purchasedSheet.purchasedSheetId);
                  Navigator.pop(context);
                  Navigator.push(context, PageTransition(child: PurchasedSheetDetailPage(detailImport,returnPurchasedSheet!=null?returnPurchasedSheet.returnPurchasedSheets:null), type: pageTransitionType)).then((value) {
                    if(value!=null){
                      if(value){
                        _pagingController.refresh();
                      }
                    }
                  });
                },
                child: Column(children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Expanded(
                          flex: 2,
                          child: Padding(
                            padding: const EdgeInsets.all(3.0),
                            child: Text(purchasedSheet
                                .purchasedSheetId
                                .toString()),
                          )),
                      Expanded(
                          flex: 5,
                          child: Padding(
                              padding: const EdgeInsets.all(3.0),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text((purchasedSheet
                                      .supplierName ==
                                      null||purchasedSheet
                                      .supplierName ==
                                      "null")
                                      ? "Nhà cung cấp lẻ"
                                      : purchasedSheet
                                      .supplierName!),
                                  (purchasedSheet.supplierPhone!=null&&purchasedSheet.supplierPhone!="null")?
                                  Text(purchasedSheet.supplierPhone!,style: TextStyle(fontWeight: FontWeight.w400,color: Colors.grey,fontSize: 13),):
                                  Container()
                                ],
                              ))),
                      Expanded(
                          flex: 6,
                          child: Padding(
                            padding: const EdgeInsets.all(3.0),
                            child: Text(formatter.format(purchasedSheet
                                .totalPurchasePrice)),
                          )),
                      Expanded(
                          flex: 4,
                          child: Padding(
                            padding: const EdgeInsets.all(3.0),
                            child: Text(formatter.format(purchasedSheet.discount)),
                          )),
                      Expanded(
                          flex: 5,
                          child: Padding(
                            padding: const EdgeInsets.all(3.0),
                            child: Text(DateFormat("dd-MM-yyyy HH:mm:ss")
                                .format(purchasedSheet
                                .deliveryDate!)),
                          ))
                    ],
                  ),
                  Divider()
                ]),
              );
          },
            noItemsFoundIndicatorBuilder: (context) {
              return Container(
                height: MediaQuery.of(context).size.height / 2,
                child: Center(
                  child: Text(
                    "Không có đơn nhập",
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
                    "Đã có lỗi xảy ra",
                    style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w300,
                        color: Colors.red),
                  ),
                ),
              );
            },
          ),
            ),
      ),
    ]);
  }
}

class ImportInvoicePage extends StatefulWidget {
  @override
  _ImportInvoicePageState createState() => _ImportInvoicePageState();
}

class _ImportInvoicePageState extends State<ImportInvoicePage> {
  late SortListCriteria sortListCriteria;
  List<PurchasedSheetInfo>? importInvoices;
  late ListPurchasedSheet listImportInvoice;
  BkrmService bkrmService = BkrmService();
  @override
  void initState() {
    super.initState();
    listImportInvoice = ListPurchasedSheet();
    sortListCriteria = SortListCriteria(listImportInvoice);
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
          title: Text("Đơn nhập hàng"),
          actions: [
            IconButton(
                icon: Icon(Icons.date_range),
                onPressed: () {
                  showDialog(
                      context: context,
                      builder: (context) {
                        return DatePickerDialog(
                          listImportInvoice.filterFrom != null
                              ? listImportInvoice.filterFrom
                              : DateTime.now(),
                          listImportInvoice.filterTo != null
                              ? listImportInvoice.filterTo
                              : DateTime.now(),
                          onDonePick: (from, to) {
                            listImportInvoice.setDateFilter(from, to);
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
        body: RefreshIndicator(
          onRefresh: () async {
            listImportInvoice.refeesh();
          },
          child: Container(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(children: [
                  Expanded(
                    child: Padding(
                      padding: const EdgeInsets.all(8.0),
                      child: TextField(
                        onSubmitted: (value) {
                          listImportInvoice.searchList(value);
                        },
                        decoration: InputDecoration(
                            labelText: "Tìm kiếm",
                            hintText: "Nhập tên nhà cung cấp",
                            prefixIcon: Icon(Icons.search),
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
                            PageTransition(child: DetailSearchPurchasedSheetPage(),type: pageTransitionType));
                      },
                      child: Text("Tìm kiếm \nchi tiết"),
                    ),
                  )
                ]),
                Expanded(
                    child: Padding(
                  padding: EdgeInsets.all(8.0),
                  child: listImportInvoice),
                )
              ],
            ),
          ),
        ),
      ),
    );
  }
}
