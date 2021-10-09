import 'package:bkrm/services/api.dart';
import 'package:bkrm/services/info/inventoryInfo/categoryInfo.dart';
import 'package:bkrm/services/info/report/itemReportInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:flutter/material.dart';
import 'package:flutter_datetime_picker/flutter_datetime_picker.dart';
import 'package:intl/intl.dart';

class ItemReportPage extends StatefulWidget {
  @override
  _ItemReportPAgeState createState() => _ItemReportPAgeState();
}

class _ItemReportPAgeState extends State<ItemReportPage> {
  String displayChartAs = "day";
  int maxLimitItems = 10;
  int categoryId = 0;
  DateTime startDate = DateTime.now().subtract(Duration(days: 7));
  DateTime endDate = DateTime.now();
  ItemReportInfo? itemReportInfo;

  List<CategoryInfo>? listCategory;

  bool selectDayManual = false;

  TextEditingController startDateController = TextEditingController();
  TextEditingController endDateController = TextEditingController();

  Future<List<CategoryInfo>> getCategoryInfo() async {
    return await BkrmService().getCategory();
  }

  @override
  void initState() {
    super.initState();
    getCategoryInfo().then((value) {
      setState(() {
        listCategory = value;
        CategoryInfo tempCategory = CategoryInfo(
            id: 0.toString(),
            name: "TẤT CẢ DANH MỤC",
            storeId: BkrmService().currentUser!.storeId.toString(),
            deleted: 0.toString(),
            createdAt: DateTime.now().toString(),
            updatedAt: DateTime.now().toString(),
            pointRatio: 0.toString());
        listCategory!.insert(0, tempCategory);
        categoryId = listCategory!.first.id!;
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text("Thống kê sản phẩm"),
      ),
      body: SingleChildScrollView(
        child: Container(
          padding: EdgeInsets.all(8.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              SizedBox(
                height: 30,
              ),
              if (selectDayManual)
                Column(
                  children: [
                    Row(
                      children: [
                        Expanded(flex: 1, child: Text("Từ: ",style: TextStyle(fontSize: 18,fontWeight: FontWeight.bold),)),
                        Expanded(
                            flex: 3,
                            child: InkWell(
                                onTap: () {
                                  DatePicker.showDatePicker(context,
                                      maxTime: endDate,
                                      currentTime: startDate,
                                      locale: LocaleType.vi,
                                      onConfirm: (DateTime? selectDate) {
                                    setState(() {
                                      if (selectDate != null) {
                                        startDate = selectDate;
                                        startDateController.text =
                                            DateFormat("dd/MM/yyyy")
                                                .format(startDate);
                                      }
                                    });
                                  });
                                },
                                child: IgnorePointer(
                                  child: TextFormField(
                                    controller: startDateController,
                                  ),
                                )))
                      ],
                    ),
                    Row(
                      children: [
                        Expanded(flex: 1, child: Text("Đến: ",style: TextStyle(fontSize: 18,fontWeight: FontWeight.bold),)),
                        Expanded(
                            flex: 3,
                            child: InkWell(
                                onTap: () {
                                  DatePicker.showDatePicker(context,
                                      minTime: startDate,
                                      currentTime: endDate,
                                      maxTime: DateTime.now(),
                                      locale: LocaleType.vi,
                                      onConfirm: (DateTime? selectDate) {
                                    setState(() {
                                      if (selectDate != null) {
                                        endDate = selectDate;
                                        endDateController.text =
                                            DateFormat("dd/MM/yyyy")
                                                .format(endDate);
                                      }
                                    });
                                  });
                                },
                                child: IgnorePointer(
                                  child: TextFormField(
                                    controller: endDateController,
                                  ),
                                )))
                      ],
                    ),
                  ],
                )
              else
                Row(
                  children: [
                    Expanded(
                        flex: 1,
                        child: Text(
                          "Hiển thị theo: ",
                          style: TextStyle(
                              fontSize: 18, fontWeight: FontWeight.bold),
                        )),
                    Expanded(
                        flex: 1,
                        child: Container(
                          child: DropdownButton(
                            isExpanded: true,
                            value: displayChartAs,
                            onChanged: (String? value) {
                              if (value != displayChartAs) {
                                if (value != null) {
                                  setState(() {
                                    displayChartAs = value;
                                    if (value == "day") {
                                      startDate = DateTime.now()
                                          .subtract(Duration(days: 7));
                                      endDate = DateTime.now();
                                    } else {
                                      if (value == "month") {
                                        startDate = DateTime.now()
                                            .subtract(Duration(days: 30));
                                        endDate = DateTime.now();
                                      } else {
                                        if (value == "year") {
                                          startDate = DateTime.now()
                                              .subtract(Duration(days: 365));
                                          endDate = DateTime.now();
                                        }
                                      }
                                    }
                                  });
                                }
                              }
                            },
                            items: [
                              DropdownMenuItem(
                                child: Text("7 ngày gần nhất"),
                                value: "day",
                              ),
                              DropdownMenuItem(
                                child: Text("30 ngày gần nhất"),
                                value: "month",
                              ),
                              DropdownMenuItem(
                                child: Text("365 ngày gần nhất"),
                                value: "year",
                              ),
                            ],
                          ),
                        )),
                  ],
                ),
              SizedBox(height: 5,),
              Center(
                child: ElevatedButton(
                  onPressed: () {
                    setState(() {
                      selectDayManual = !selectDayManual;
                      if (selectDayManual) {
                        startDateController.text =
                            DateFormat("dd/MM/yyyy").format(startDate);
                        endDateController.text =
                            DateFormat("dd/MM/yyyy").format(endDate);
                      } else {
                        startDate = DateTime.now().subtract(Duration(days: 7));
                        endDate = DateTime.now();
                        displayChartAs = "day";
                      }
                    });
                  },
                  child: Text(selectDayManual
                      ? "Chọn theo ngày gần nhất"
                      : "Chọn ngày thủ công"),
                ),
              ),
              Row(
                children: [
                  Expanded(
                      flex: 1,
                      child: Text(
                        "Số lượng: ",
                        style: TextStyle(
                            fontSize: 18, fontWeight: FontWeight.bold),
                      )),
                  Expanded(
                      flex: 1,
                      child: Container(
                        child: DropdownButton(
                          isExpanded: true,
                          value: maxLimitItems,
                          onChanged: (int? value) {
                            if (value != maxLimitItems) {
                              if (value != null) {
                                setState(() {
                                  maxLimitItems = value;
                                });
                              }
                            }
                          },
                          items: [
                            DropdownMenuItem(
                              child: Text("Tối đa 10 sản phẩm"),
                              value: 10,
                            ),
                            DropdownMenuItem(
                              child: Text("Tối đa 20 sản phẩm"),
                              value: 20,
                            ),
                            DropdownMenuItem(
                              child: Text("Tối đa 50 sản phẩm"),
                              value: 50,
                            ),
                            DropdownMenuItem(
                              child: Text("Tối đa 100 sản phẩm"),
                              value: 100,
                            ),
                          ],
                        ),
                      )),
                ],
              ),
              Row(
                children: [
                  Expanded(
                      flex: 1,
                      child: Text(
                        "Danh mục: ",
                        style: TextStyle(
                            fontSize: 18, fontWeight: FontWeight.bold),
                      )),
                  Expanded(
                      flex: 1,
                      child: Container(
                        child: DropdownButton(
                          isExpanded: true,
                          value: categoryId,
                          onChanged: (int? value) {
                            if (value != categoryId) {
                              if (value != null) {
                                setState(() {
                                  categoryId = value;
                                });
                              }
                            }
                          },
                          items: listCategory == null
                              ? [
                                  DropdownMenuItem(
                                    child: Text("Loading..."),
                                    value: 0,
                                  )
                                ]
                              : listCategory!.map((CategoryInfo category) {
                                  return DropdownMenuItem(
                                    child: Text(category.name),
                                    value: category.id,
                                  );
                                }).toList(),
                        ),
                      )),
                ],
              ),
              SizedBox(
                height: 20,
              ),
              ElevatedButton(
                  onPressed: () async {
                    showDialog(
                        context: context,
                        builder: (context) {
                          return AlertDialog(
                            content: Container(
                              height: 50,
                              child: Center(child: CircularProgressIndicator()),
                            ),
                          );
                        });
                    itemReportInfo = await BkrmService().getReportItems(
                        fromDate: startDate,
                        toDate: endDate,
                        maxLimitItems: maxLimitItems,
                        categoryId: categoryId);
                    Navigator.pop(context);
                    setState(() {});
                  },
                  child: Container(
                    padding: EdgeInsets.all(10.0),
                    child: Text("Xem thống kê"),
                  )),
              SizedBox(
                height: 30,
              ),
              itemReportInfo == null
                  ? Container(
                      height: 300,
                      width: MediaQuery.of(context).size.width,
                      child: Center(
                        child: Text(
                          "Không có dữ liệu",
                          style: TextStyle(
                              fontSize: 20,
                              color: Colors.grey,
                              fontWeight: FontWeight.w300),
                        ),
                      ),
                    )
                  : Container(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            "Sản phẩm có doanh thu cao nhất :",
                            style: TextStyle(
                                fontWeight: FontWeight.bold, fontSize: 16),
                          ),
                          SizedBox(
                            height: 10,
                          ),
                          ListView.separated(
                              physics: NeverScrollableScrollPhysics(),
                              shrinkWrap: true,
                              itemBuilder: (context, index) {
                                return Row(
                                  children: [
                                    Expanded(
                                      flex: 2,
                                      child: itemReportInfo!
                                                  .top3TotalSellPriceItems[
                                                      index]
                                                  .imageUrl ==
                                              "null"
                                          ? Image.asset(
                                              "asset/productImage/no-image.jpg")
                                          : Image.network(
                                              ServerConfig.projectUrl +
                                                  itemReportInfo!
                                                      .top3TotalSellPriceItems[
                                                          index]
                                                      .imageUrl),
                                    ),
                                    Expanded(
                                        flex: 8,
                                        child: Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              itemReportInfo!
                                                  .top3TotalSellPriceItems[
                                                      index]
                                                  .name,
                                              style: TextStyle(
                                                  fontWeight: FontWeight.bold),
                                            ),
                                            Text("Số lượng bán :" +
                                                NumberFormat().format(
                                                    itemReportInfo!
                                                        .top3TotalSellPriceItems[
                                                            index]
                                                        .totalQuantity)),
                                            Text("Tổng tiền :" +
                                                NumberFormat().format(
                                                    itemReportInfo!
                                                        .top3TotalSellPriceItems[
                                                            index]
                                                        .totalSellPrice) +
                                                " VNĐ"),
                                          ],
                                        ))
                                  ],
                                );
                              },
                              separatorBuilder: (context, index) => Divider(),
                              itemCount: itemReportInfo!
                                  .top3TotalSellPriceItems.length),
                          Divider(),
                          SizedBox(
                            height: 30,
                          ),
                          Text(
                            "Sản phẩm có số lượng bán nhiều nhất :",
                            style: TextStyle(
                                fontWeight: FontWeight.bold, fontSize: 16),
                          ),
                          SizedBox(
                            height: 10,
                          ),
                          ListView.separated(
                              physics: NeverScrollableScrollPhysics(),
                              shrinkWrap: true,
                              itemBuilder: (context, index) {
                                return Row(
                                  children: [
                                    Expanded(
                                      flex: 2,
                                      child: itemReportInfo!
                                                  .top3SoldQuantityItems[index]
                                                  .imageUrl ==
                                              "null"
                                          ? Image.asset(
                                              "asset/productImage/no-image.jpg")
                                          : Image.network(ServerConfig
                                                  .projectUrl +
                                              itemReportInfo!
                                                  .top3SoldQuantityItems[index]
                                                  .imageUrl),
                                    ),
                                    Expanded(
                                        flex: 8,
                                        child: Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              itemReportInfo!
                                                  .top3SoldQuantityItems[index]
                                                  .name,
                                              style: TextStyle(
                                                  fontWeight: FontWeight.bold),
                                            ),
                                            Text("Số lượng bán :" +
                                                NumberFormat().format(
                                                    itemReportInfo!
                                                        .top3SoldQuantityItems[
                                                            index]
                                                        .totalQuantity)),
                                            Text("Tổng tiền :" +
                                                NumberFormat().format(
                                                    itemReportInfo!
                                                        .top3SoldQuantityItems[
                                                            index]
                                                        .totalSellPrice) +
                                                " VNĐ"),
                                          ],
                                        ))
                                  ],
                                );
                              },
                              separatorBuilder: (context, index) => Divider(),
                              itemCount:
                                  itemReportInfo!.top3SoldQuantityItems.length),
                          Divider(),
                          SizedBox(
                            height: 10,
                          ),
                          Text(
                            "Tổng doanh thu :" +
                                NumberFormat()
                                    .format(itemReportInfo!.totalSellPrice),
                            style: TextStyle(
                                fontWeight: FontWeight.bold, fontSize: 16),
                          ),
                          Text(
                            "Tổng số lượng hàng đã bán :" +
                                NumberFormat()
                                    .format(itemReportInfo!.totalSellQuantity),
                            style: TextStyle(
                                fontWeight: FontWeight.bold, fontSize: 16),
                          ),
                          SizedBox(
                            height: 10,
                          ),
                        ],
                      ),
                    )
            ],
          ),
        ),
      ),
    );
  }
}
