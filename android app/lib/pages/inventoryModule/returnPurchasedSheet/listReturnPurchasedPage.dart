import 'package:bkrm/pages/inventoryModule/returnPurchasedSheet/detailSearchReturnPurchasedPage.dart';
import 'package:bkrm/pages/inventoryModule/returnPurchasedSheet/returnPurchasedDetailPage.dart';
import 'package:bkrm/pages/sellerModule/refund/detailSearchRefundPage.dart';
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
import 'package:page_transition/page_transition.dart';

import 'package:bkrm/pages/Nav2App.dart';

enum SortCriteria {
  refunderNameAscending,
  refunderNameDescending,
  totalRefundPriceAscending,
  totalRefundPriceDescending,
  customerNameAscending,
  customerNameDescending,
  dateCreatedAscending,
  dateCreatedDescending
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
        title: Text("Chọn khoảng thời gian để lọc"),
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
  final ListReturnPurchasedSheet listRefunds;
  SortCriteria? sortCriteria = SortCriteria.dateCreatedDescending;
  Criteria? selectedCriteria = Criteria.date;
  SortListCriteria(this.listRefunds);

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
      case Criteria.sellerName:
        radioButtonGroup = Column(
          children: [
            Row(
              children: [
                Text("Theo thứ tự thấp đến cao "),
                Radio(
                    value: SortCriteria.refunderNameAscending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listRefunds.sortReturnPurchasedSheets(orderBy:"returner_name",order: "asc");
                      Navigator.pop(context);
                    })
              ],
            ),
            Row(
              children: [
                Text("Theo thứ tự cao đến thấp "),
                Radio(
                    value: SortCriteria.refunderNameDescending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listRefunds.sortReturnPurchasedSheets(orderBy:"returner_name",order: "desc");
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
                    value: SortCriteria.totalRefundPriceAscending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listRefunds.sortReturnPurchasedSheets(orderBy:"total_return_money",order: "asc");
                      Navigator.pop(context);
                    })
              ],
            ),
            Row(
              children: [
                Text("Theo thứ tự cao đến thấp "),
                Radio(
                    value: SortCriteria.totalRefundPriceDescending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listRefunds.sortReturnPurchasedSheets(orderBy:"total_return_money",order: "desc");
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
                    value: SortCriteria.dateCreatedAscending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listRefunds.sortReturnPurchasedSheets(orderBy:"created_datetime",order: "asc");
                      Navigator.pop(context);
                    })
              ],
            ),
            Row(
              children: [
                Text("Theo thứ tự cao đến thấp "),
                Radio(
                    value: SortCriteria.dateCreatedDescending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listRefunds.sortReturnPurchasedSheets(orderBy: "created_datetime",order: "desc");
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
                    child: Text("Tổng tiền trả"),
                    value: Criteria.price,
                  ),
                  DropdownMenuItem(
                    child: Text("Tên người thực hiện"),
                    value: Criteria.sellerName,
                  ),
                  DropdownMenuItem(
                    child: Text("Ngày trả"),
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

class ListReturnPurchasedSheet extends StatefulWidget {
  late List<ReturnPurchasedSheetInfo> displayListRefundSheets;
  String orderBy = "created_datetime";
  String order = "desc";
  String searchQuery = "";
  late _ListReturnPurchasedSheetState _state;
  DateTime? filterFrom;
  DateTime? filterTo;
  int? searchId;
  int? totalMoneyFrom;
  int? totalMoneyTo;

  ListReturnPurchasedSheet(
  {
    DateTime? filterFrom,
    DateTime? filterTo,
    int? searchId,
    int? totalMoneyFrom,
    int? totalMoneyTo,
    String? searchQuery,
  }) {
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

  void sortReturnPurchasedSheets({required String orderBy,required String order}) {
    this.orderBy=orderBy;
    this.order=order;
    _state._pagingController.refresh();
  }

  @override
  _ListReturnPurchasedSheetState createState() {
    _state = _ListReturnPurchasedSheetState();
    return _state;
  }
}

class _ListReturnPurchasedSheetState extends State<ListReturnPurchasedSheet> {
  final PagingController<int, ReturnPurchasedSheetInfo> _pagingController =
  PagingController(firstPageKey: 1);
  NumberFormat formatter = NumberFormat();


  @override
  void initState() {
    _pagingController.addPageRequestListener((pageKey) {_fetchPage(pageKey);});
    super.initState();
  }

  Future<void> _fetchPage(int pageKey) async {
    try {
      debugPrint("Pagekey " + pageKey.toString());
      debugPrint("Fetch page return");
      ReturnPurchasedSheetPagination? itemPage = await BkrmService().getReturnPurchasedSheet(
          page: pageKey,
          orderBy: widget.orderBy,
          order: widget.order,
          returnPurchasedSheetId: widget.searchId,
          purchasedSheetId: widget.searchId,
          keyword: widget.searchQuery,
          totalMoneyFrom: widget.totalMoneyFrom,
          totalMoneyTo: widget.totalMoneyTo,
          createdFrom: widget.filterFrom,
          createdTo: widget.filterTo);
      debugPrint("End get return purchased sheet");
      if (itemPage == null) {
        _pagingController.appendLastPage([]);
        return;
      }
      if (itemPage.currentPage == itemPage.lastPage) {
        debugPrint("last page");
        _pagingController.appendLastPage(itemPage.returnPurchasedSheets);
      } else {
        final nextPageKey = pageKey + 1;
        _pagingController.appendPage(itemPage.returnPurchasedSheets, nextPageKey);
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
                  "Tổng tiền hoàn trả",
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              )),
          Expanded(
              flex: 4,
              child: Padding(
                padding: const EdgeInsets.all(3.0),
                child: Text(
                  "Người thực hiện",
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              )),
          Expanded(
              flex: 5,
              child: Padding(
                padding: const EdgeInsets.all(3.0),
                child: Text(
                  "Ngày trả",
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
          onRefresh: ()async{
            _pagingController.refresh();
          },
          child: PagedListView.separated(
              shrinkWrap: true,
              builderDelegate: PagedChildBuilderDelegate(
                  itemBuilder: (context, ReturnPurchasedSheetInfo returnPurchasedSheet,index) {
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
                        DetailReturnPurchasedSheetInfo? detailReturnPurchasedSheet = await BkrmService()
                            .getDetailReturnPurchasedSheet(
                            returnPurchasedSheet)
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
                        Navigator.pop(context);
                        Navigator.push(context, PageTransition(child: ReturnPurchasedSheetDetail(detailReturnPurchasedSheet), type: pageTransitionType));
                      },
                      child: Column(children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Expanded(
                                flex: 2,
                                child: Padding(
                                  padding: const EdgeInsets.all(3.0),
                                  child: Text(returnPurchasedSheet.returnPurchasedSheetId
                                      .toString()),
                                )),
                            Expanded(
                                flex: 5,
                                child: Padding(
                                  padding: const EdgeInsets.all(3.0),
                                  child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(returnPurchasedSheet
                                            .supplierName==
                                            null
                                            ? "Nhà cung cấp lẻ"
                                            : returnPurchasedSheet.supplierName!
                                            ),
                                        (returnPurchasedSheet
                                            .supplierName !=
                                            null&&returnPurchasedSheet.supplierName !="null")
                                            ? Text(
                                          returnPurchasedSheet
                                              .supplierName!,
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
                                  child: Text(formatter.format(returnPurchasedSheet
                                      .totalReturnMoney)),
                                )),
                            Expanded(
                                flex: 4,
                                child: Padding(
                                  padding: const EdgeInsets.all(3.0),
                                  child: Text(returnPurchasedSheet.returnerName),
                                )),
                            Expanded(
                                flex: 5,
                                child: Padding(
                                  padding: const EdgeInsets.all(3.0),
                                  child: Text(DateFormat("dd-MM-yyyy HH:mm:ss")
                                      .format(returnPurchasedSheet
                                      .createdDateTime)),
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
                        "Không có đơn trả hàng",
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
              ), pagingController: _pagingController, separatorBuilder: (BuildContext context, int index) { return Divider(); },
              ),
        ),
      ),
    ]);
  }
}

class ListReturnPurchasedPage extends StatefulWidget {
  @override
  _ListReturnPurchasedPageState createState() => _ListReturnPurchasedPageState();
}

class _ListReturnPurchasedPageState extends State<ListReturnPurchasedPage> {

  late SortListCriteria sortListCriteria;
  List<ReturnPurchasedSheetInfo>? refundSheets ;
  ListReturnPurchasedSheet listReturnPurchasedSheets =ListReturnPurchasedSheet();
  BkrmService bkrmService = BkrmService();
  @override
  void initState() {
    super.initState();
    sortListCriteria = SortListCriteria(listReturnPurchasedSheets);
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
          title: Text("Trả hàng nhập"),
          actions: [
            IconButton(
                icon: Icon(Icons.date_range),
                onPressed: () {
                  showDialog(
                      context: context,
                      builder: (context) {
                        return DatePickerDialog(
                          listReturnPurchasedSheets.filterFrom != null
                              ? listReturnPurchasedSheets.filterFrom
                              : DateTime.now(),
                          listReturnPurchasedSheets.filterTo != null
                              ? listReturnPurchasedSheets.filterTo
                              : DateTime.now(),
                          onDonePick: (from, to) {
                            listReturnPurchasedSheets.setDateFilter(from, to);
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
                  }
                )
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
                      onSubmitted: (value) {
                        listReturnPurchasedSheets.searchList(value);
                      },
                      decoration: InputDecoration(
                          labelText: "Tìm kiếm",
                          hintText: "Nhập tên hoặc số điện thoại",
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
                          PageTransition(child: DetailSearchReturnPurchasedSheetPage(), type: pageTransitionType));
                    },
                    child: Text("Tìm kiếm \nchi tiết"),
                  ),
                )
              ]),
              Expanded(
                  child: Padding(
                padding: EdgeInsets.all(8.0),
                child: listReturnPurchasedSheets
              ))
            ],
          ),
        ),
      ),
    );
  }
}
