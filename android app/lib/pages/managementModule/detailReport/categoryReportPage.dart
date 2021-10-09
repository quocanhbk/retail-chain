import 'package:bkrm/services/info/report/categoryReportInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';
import 'package:flutter_datetime_picker/flutter_datetime_picker.dart';
import 'package:flutter_palette/flutter_palette.dart';
import 'package:intl/intl.dart';

class ColorNameCategory {
  Color color;
  int id;

  ColorNameCategory(this.color, this.id);

  @override
  String toString() {
    return "Color:"+color.toString()+" ,id:"+id.toString();
  }
}

class ColorCategoryPresent extends StatefulWidget {
  int index;
  Color color;
  String value;
  Function(int) callBackWhenTapDown;
  Function(int) callbackWhenTapUp;

  ColorCategoryPresent(this.index, this.color, this.value,
      this.callBackWhenTapDown, this.callbackWhenTapUp);

  @override
  _ColorCategoryPresentState createState() => _ColorCategoryPresentState();
}

class _ColorCategoryPresentState extends State<ColorCategoryPresent> {
  @override
  Widget build(BuildContext context) {
    return Expanded(
      flex: 1,
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Expanded(
              flex: 1,
              child: GestureDetector(
                onTapDown: (_) {
                  widget.callBackWhenTapDown(widget.index);
                },
                onTapUp: (_) {
                  debugPrint("On tap up");
                  setState(() {
                    widget.callbackWhenTapUp(widget.index);
                  });
                },
                child: Center(
                  child: Container(
                      color: widget.color,
                      child: SizedBox(
                        height: 10,
                        width: 10,
                      )),
                ),
              )),
          Expanded(
              flex: 1,
              child: GestureDetector(
                onTapDown: (_) {
                  widget.callBackWhenTapDown(widget.index);
                },
                onTapUp: (_) {
                  widget.callbackWhenTapUp(widget.index);
                },
                child: Text(widget.value),
              ))
        ],
      ),
    );
  }
}

class CategoryReportPage extends StatefulWidget {
  @override
  _CategoryReportPageState createState() => _CategoryReportPageState();
}

class _CategoryReportPageState extends State<CategoryReportPage> {
  String displayChartAs = "day";
  DateTime startDate = DateTime.now().subtract(Duration(days: 7));
  DateTime endDate = DateTime.now();
  CategoryReportInfo? categoryReportInfo;
  int touchedIndex = -1;
  List<ColorNameCategory>? categoryColorsName;
  List<Widget>? categoryColorWidget;

  bool selectDayManual = false;

  TextEditingController startDateController = TextEditingController();
  TextEditingController endDateController = TextEditingController();

  List<PieChartSectionData> showingSectionsTotalPrice() {
    int i = -1;
    return categoryReportInfo!.totalSellPriceCategory.map((e) {
      i += 1;
      final isTouched = i == touchedIndex;
      final fontSize = isTouched ? 25.0 : 16.0;
      final radius = isTouched ? 60.0 : 50.0;
      Color colorSection = categoryColorsName!
          .singleWhere((element) => e.id == element.id)
          .color;
      return PieChartSectionData(
          showTitle: isTouched,
          color: colorSection,
          value: (e.totalSellPrice / categoryReportInfo!.totalSellPrice) * 100,
          radius: radius,
          title:
              (((e.totalSellPrice / categoryReportInfo!.totalSellPrice) * 100)
                      .toInt()
                      .toString() +
                  "%"),
          titleStyle: TextStyle(
              color: colorSection.computeLuminance() > 0.5
                  ? Colors.black
                  : Colors.white,
              fontSize: fontSize));
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text("Thống kê danh mục"),
      ),
      body: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(8.0),
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
                    categoryReportInfo = await BkrmService().getReportCategories(
                        fromDate: startDate, toDate: endDate);

                    if(categoryReportInfo==null){
                      Navigator.pop(context);
                      return;
                    }
                    if(categoryReportInfo!.totalSellPriceCategory.isEmpty){
                      categoryReportInfo=null;
                      Navigator.pop(context);
                      return;
                    }
                    List<Color> colors = ColorPalette.polyad(
                      Color(0xFF00FFFF),
                      numberOfColors:
                          categoryReportInfo!.totalSellPriceCategory.length,
                      hueVariability: 15,
                      saturationVariability: 10,
                      brightnessVariability: 10,
                    ).toList();
                    categoryColorsName = [];
                    int i = 0;
                    for (var category
                        in categoryReportInfo!.totalSellPriceCategory) {
                      categoryColorsName!
                          .add(ColorNameCategory(colors[i], category.id));
                      i += 1;
                    }

                    i = 0;
                    categoryColorWidget = [];
                    while (
                        i < categoryReportInfo!.totalSellPriceCategory.length) {
                      List<Widget> listWidget = [];
                      int j = 0;
                      while (j < 3) {
                        if (i * 3 + j >=
                            categoryReportInfo!.totalSellPriceCategory.length) {
                          listWidget.add(Expanded(flex: 1, child: Container()));
                          j += 1;
                          continue;
                        }
                        Color currentColor=Colors.white;
                        for(var current in categoryColorsName!){
                          if(current.id==categoryReportInfo!.totalSellPriceCategory[i*3+j].id){
                            currentColor=current.color;
                            break;
                          }
                        }
                        String currentName = categoryReportInfo!
                            .totalSellPriceCategory[i * 3 + j].name;
                        listWidget.add(ColorCategoryPresent(
                            i * 3 + j,
                            currentColor
                            ,
                            currentName
                            , (index) {
                          setState(() {
                            touchedIndex = index;
                          });
                        }, (index) {
                          setState(() {
                            touchedIndex = -1;
                          });
                        }));
                        j += 1;
                      }
                      categoryColorWidget!.add(Row(
                        mainAxisSize: MainAxisSize.min,
                        children: listWidget,
                      ));
                      if (i * 3 + j >=
                          categoryReportInfo!.totalSellPriceCategory.length) {
                        break;
                      }
                      i += 1;
                    }
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
              categoryReportInfo == null
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
                  : Column(
                      children: [
                        Row(
                          children: [
                            Expanded(
                              flex: 1,
                              child: Padding(
                                padding: const EdgeInsets.all(8.0),
                                child: Column(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Text(
                                      "Doanh thu :",
                                      style: TextStyle(
                                          fontSize: 14,
                                          fontWeight: FontWeight.bold),
                                    ),
                                    SizedBox(
                                      height: 20,
                                    ),
                                    Container(
                                      height: 200,
                                      child: PieChart(PieChartData(
                                          pieTouchData: PieTouchData(
                                              touchCallback: (pieTouchResponse) {
                                            setState(() {
                                              final desiredTouch =
                                                  pieTouchResponse.touchInput
                                                          is! PointerExitEvent &&
                                                      pieTouchResponse.touchInput
                                                          is! PointerUpEvent;
                                              if (desiredTouch &&
                                                  pieTouchResponse
                                                          .touchedSection !=
                                                      null) {
                                                touchedIndex = pieTouchResponse
                                                    .touchedSection!
                                                    .touchedSectionIndex;
                                              } else {
                                                touchedIndex = -1;
                                              }
                                            });
                                          }),
                                          sections: showingSectionsTotalPrice())),
                                    )
                                  ],
                                ),
                              ),
                            ),
                          ],
                        ),
                        Column(
                          children: categoryColorWidget!,
                        ),
                        SizedBox(
                          height: 20,
                        ),
                        Text(
                          "Chi tiết doanh thu: ",
                          style: TextStyle(
                              fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                        SizedBox(
                          height: 10,
                        ),
                        Padding(
                          padding: const EdgeInsets.all(8.0),
                          child: ListView.separated(
                              shrinkWrap: true,
                              physics: NeverScrollableScrollPhysics(),
                              itemBuilder: (context, index) {
                                return Row(
                                  children: [
                                    Expanded(
                                      flex: 2,
                                      child: Text(
                                        categoryReportInfo!
                                            .totalSellPriceCategory[index].name,
                                        style: TextStyle(fontSize: 14),
                                      ),
                                    ),
                                    Expanded(
                                        flex: 1,
                                        child: Text(NumberFormat().format(
                                                categoryReportInfo!
                                                    .totalSellPriceCategory[index]
                                                    .totalSellPrice) +
                                            " VNĐ/" +
                                            NumberFormat().format(
                                                categoryReportInfo!
                                                        .totalSellPriceCategory[
                                                            index]
                                                        .totalSellPrice *
                                                    100 /
                                                    categoryReportInfo!
                                                        .totalSellPrice) +
                                            "%")),
                                  ],
                                );
                              },
                              separatorBuilder: (context, index) {
                                return Divider();
                              },
                              itemCount: categoryReportInfo!
                                  .totalSellPriceCategory.length),
                        ),
                        SizedBox(
                          height: 20,
                        ),
                      ],
                    )
            ],
          ),
        ),
      ),
    );
  }
}
