import 'dart:math';

import 'package:bkrm/pages/managementModule/detailReport/listItemNoPurchasePrice.dart';
import 'package:bkrm/services/info/report/chartInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter_datetime_picker/flutter_datetime_picker.dart';
import 'package:intl/intl.dart';

class ChartPage extends StatefulWidget {
  @override
  _ChartPageState createState() => _ChartPageState();
}


class YearModel extends DatePickerModel {
  YearModel (
      {DateTime? currentTime,
        DateTime? maxTime,
        DateTime? minTime,
        LocaleType? locale})
      : super(
      currentTime: currentTime,
      maxTime: maxTime,
      minTime: minTime,
      locale: locale);

  @override
  List<int> layoutProportions() {
    return [1,0,0];
  }

}

class YearMonthModel extends DatePickerModel {
  YearMonthModel (
      {DateTime? currentTime,
        DateTime? maxTime,
        DateTime? minTime,
        LocaleType? locale=LocaleType.vi})
      : super(
      currentTime: currentTime,
      maxTime: maxTime,
      minTime: minTime,
      locale: locale);

  @override
  List<int> layoutProportions() {
    return [1,1,0];
  }
}

class _ChartPageState extends State<ChartPage> {
  GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey();

  String displayChartAs = "day";

  TextEditingController startDateController = TextEditingController();
  TextEditingController endDateController = TextEditingController();

  DateTime startDate = DateTime.now();
  DateTime endDate =  DateTime.now();

  ChartInfo? chartInfo;
  List<BarChartGroupData> listBarData = [];

  Color leftBarColor = Colors.green;
  Color middleBarColor = Colors.blue;
  Color rightBarColor = Colors.red;

  double width = 7;


  @override
  void initState() {
    startDateController.text=DateFormat("dd-MM-yyyy").format(startDate);
    endDateController.text=DateFormat("dd-MM-yyyy").format(endDate);
    super.initState();
  }

  buildBarChartGroup(ChartInfo chartInfo) {
    listBarData.clear();
    listBarData.add(makeGroupData(-1, 0,0,
        0));
    for (int i = 0; i < chartInfo.revenue.length ; i++) {
      double revenue = chartInfo.revenue[i].toDouble();
      double purchase = chartInfo.purchase[i].toDouble();
      double profit = chartInfo.profit[i].toDouble();
      debugPrint("Add: "+revenue.toString()+" ,"+profit.toString()+" ,"+purchase.toString());
      listBarData.add(makeGroupData(i, revenue,purchase,
          profit));
    }
    listBarData.add(makeGroupData(chartInfo.revenue.length, 0,0,
        0));
  }

  BarChartGroupData makeGroupData(int x, double y1, double y2, double y3,{String? tooltip}) {
    double barsSpace = 1;
    return BarChartGroupData(barsSpace: barsSpace, x: x, barRods: [
      BarChartRodData(
        borderRadius: BorderRadius.zero,
        y: y1,
        colors: [leftBarColor],
        width: width,
      ),
      BarChartRodData(
        borderRadius: BorderRadius.zero,
        y: y2,
        colors: [middleBarColor],
        width: width,
      ),
      BarChartRodData(
        borderRadius: BorderRadius.zero,
        y: y3,
        colors: [rightBarColor],
        width: width,
      ),
    ]);
  }

  double findInterval(double maxValue){
    int i=0;
    double tempValue = maxValue;
    while(tempValue>100){
      tempValue=tempValue/10;
      i+=1;
    }
    int j = 1;
    while(maxValue/(j*pow(10,i))>20){
      j+=1;
    }
    return j*pow(10,i).toDouble();
  }

  @override
  Widget build(BuildContext context) {
    debugPrint(chartInfo.toString());
    return Scaffold(
      key: _scaffoldKey,
      appBar: AppBar(
        title: Text("Biểu đồ chi tiết"),
      ),
      body: SingleChildScrollView(
        child: Container(
          padding: EdgeInsets.all(8.0),
          child: Column(
            children: [
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
                                  if(value=="day"){
                                    startDateController.text=DateFormat("dd-MM-yyyy").format(startDate);
                                    startDate=DateTime(startDate.year,startDate.month,startDate.day);
                                    if(endDate.isAfter(DateTime.now())){
                                      endDate=DateTime.now();
                                    }
                                    endDateController.text=DateFormat("dd-MM-yyyy").format(endDate);
                                  }else{
                                    if(value=="month"){
                                      startDateController.text=DateFormat("MM-yyyy").format(startDate);
                                      startDate=DateTime(startDate.year,startDate.month);
                                      if(endDate.isAfter(DateTime.now())){
                                        endDate=DateTime.now();
                                      }else{
                                        endDate=DateTime(endDate.year,endDate.month+1);
                                        endDate = endDate.subtract(Duration(days: 1));
                                      }
                                      endDateController.text=DateFormat("MM-yyyy").format(endDate);

                                    }else{
                                      if(value=="year"){
                                        startDateController.text=DateFormat("yyyy").format(startDate);
                                        startDate=DateTime(startDate.year);
                                        if(endDate.isAfter(DateTime.now())){
                                          endDate=DateTime.now();
                                        }else{
                                          endDate=endDate.subtract(Duration(days: 1));
                                        }
                                        endDateController.text=DateFormat("yyyy").format(endDate);
                                        endDate=DateTime(endDate.year+1);

                                      }
                                    }
                                  }
                                });
                              }
                            }
                          },
                          items: [
                            DropdownMenuItem(
                              child: Text("Ngày"),
                              value: "day",
                            ),
                            DropdownMenuItem(
                              child: Text("Tháng"),
                              value: "month",
                            ),
                            DropdownMenuItem(
                              child: Text("Năm"),
                              value: "year",
                            ),
                          ],
                        ),
                      )),
                ],
              ),
              SizedBox(
                height: 20,
              ),
              Container(
                  alignment: Alignment.centerLeft,
                  child: Text(
                    "Thời gian:",
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  )),
              SizedBox(
                height: 10,
              ),
              Row(
                children: [
                  Expanded(
                      flex: 1,
                      child: Text(
                        "Từ:",
                        style: TextStyle(
                            fontSize: 16, fontWeight: FontWeight.w400),
                      )),
                  Expanded(
                    flex: 3,
                    child: InkWell(
                      onTap: () {
                        if(displayChartAs=="day"){
                          DatePicker.showDatePicker(context,
                              locale: LocaleType.vi,
                              maxTime: endDate,
                              currentTime: startDate,
                              onConfirm: (startDate) {
                                setState(() {
                                  this.startDate = DateTime(startDate.year,startDate.month,startDate.day);
                                  this.startDateController.text =
                                      DateFormat("dd-MM-yyyy").format(startDate);
                                });
                              });
                          return;
                        }
                        if(displayChartAs=="month"){
                          DatePicker.showPicker(context,
                              locale: LocaleType.vi,
                              pickerModel: YearMonthModel(
                              locale: LocaleType.vi,
                              maxTime: endDate,
                            currentTime: startDate,
                          ),
                              onConfirm: (startDate) {
                                setState(() {
                                  this.startDate=DateTime(startDate.year,startDate.month);
                                  this.startDateController.text =
                                      DateFormat("MM-yyyy").format(startDate);
                                });
                              });
                          return;
                        }
                        if(displayChartAs=="year"){
                          DatePicker.showPicker(context,
                              locale: LocaleType.vi,
                              pickerModel: YearModel(
                            locale: LocaleType.vi,
                            maxTime: endDate,
                            currentTime: startDate,
                          ),
                              onConfirm: (startDate) {
                                setState(() {
                                  this.startDate=DateTime(startDate.year);
                                  this.startDateController.text =
                                      DateFormat("yyyy").format(startDate);
                                });
                              });
                          return;
                        }
                      },
                      child: IgnorePointer(
                        child: TextFormField(
                          controller: startDateController,
                        ),
                      ),
                    ),
                  ),
                  Expanded(
                      flex: 1,
                      child: Text(
                        "Đến:",
                        style: TextStyle(
                            fontSize: 16, fontWeight: FontWeight.w400),
                      )),
                  Expanded(
                      flex: 3,
                      child: InkWell(
                        onTap: () {
                          if(displayChartAs=="day"){
                            DatePicker.showDatePicker(context,
                                locale: LocaleType.vi,
                                maxTime: DateTime.now(),
                                currentTime: endDate,
                                minTime: startDate, onConfirm: (end) {
                                  setState(() {
                                    this.endDate = DateTime(end.year,end.month,end.day);
                                    this.endDateController.text =
                                        DateFormat("dd-MM-yyyy").format(end);
                                  });
                                });
                            return;
                          }
                          if(displayChartAs=="month"){
                            DatePicker.showPicker(context,
                                locale: LocaleType.vi,
                                pickerModel: YearMonthModel(
                              locale: LocaleType.vi,
                              maxTime: DateTime.now(),
                              currentTime: endDate,
                              minTime: startDate,
                            ),
                                onConfirm: (end) {
                                  setState(() {
                                    if(end.month==DateTime.now().month){
                                      this.endDate = DateTime(end.year,end.month,DateTime.now().day);
                                    }else{
                                      this.endDate = DateTime(end.year,end.month+1);
                                      this.endDate.subtract(Duration(days: 1));
                                    }
                                    this.endDateController.text =
                                        DateFormat("MM-yyyy").format(end);
                                  });
                                });
                            return;
                          }
                          if(displayChartAs=="year"){
                            DatePicker.showPicker(context,
                                locale: LocaleType.vi,
                                pickerModel: YearModel(
                              locale: LocaleType.vi,
                              maxTime: DateTime.now(),
                              currentTime: endDate,
                              minTime: startDate,
                            ),
                                onConfirm: (end) {
                                  setState(() {
                                    if(end.year==DateTime.now().year){
                                      this.endDate = DateTime(end.year,DateTime.now().month,DateTime.now().day);
                                    }else{
                                      this.endDate=DateTime(end.year+1);
                                      this.endDate.subtract(Duration(days: 1));
                                    }
                                    this.endDateController.text =
                                        DateFormat("yyyy").format(end);
                                  });
                                });
                            return;
                          }
                        },
                        child: IgnorePointer(
                          child: TextFormField(
                            controller: endDateController,
                          ),
                        ),
                      )),
                ],
              ),
              SizedBox(
                height: 20,
              ),
              Center(
                child: ElevatedButton(
                  child: Container(
                    padding: EdgeInsets.all(8.0),
                    color: Colors.blue,
                    child: Text("Xem biểu đồ"),
                  ),
                  onPressed: () async {
                    if(displayChartAs=="day"){
                      DateTime tempDate = DateTime(endDate.year,endDate.month,endDate.day-60);
                      if(tempDate.isAfter(startDate)){
                        showDialog(context: context, builder: (context){
                          return AlertDialog(
                            title: Text("Khoảng thời gian không được lớn hơn 60 ngày"),
                            actions: [
                              ElevatedButton(onPressed: (){
                                Navigator.pop(context);
                              }, child: Text("Đóng"))
                            ],
                          );
                        });
                        return;
                      }
                    }
                    if(displayChartAs=="month"){
                      DateTime tempDate = DateTime(endDate.year,endDate.month-60);
                      if(tempDate.isAfter(startDate)){
                        showDialog(context: context, builder: (context){
                          return AlertDialog(
                            title: Text("Khoảng thời gian không được lớn hơn 60 tháng"),
                            actions: [
                              ElevatedButton(onPressed: (){
                                Navigator.pop(context);
                              }, child: Text("Đóng"))
                            ],
                          );
                        });
                        return;
                      }
                    }
                    if(displayChartAs=="year"){
                      DateTime tempDate = DateTime(endDate.year-60);
                      if(tempDate.isAfter(startDate)){
                        showDialog(context: context, builder: (context){
                          return AlertDialog(
                            title: Text("Khoảng thời gian không được lớn hơn 60 năm"),
                            actions: [
                              ElevatedButton(onPressed: (){
                                Navigator.pop(context);
                              }, child: Text("Đóng"))
                            ],
                          );
                        });
                        return;
                      }
                    }
                    if(endDate.isAfter(DateTime.now())){
                      endDate=DateTime.now();
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
                    chartInfo = await BkrmService().getCharts(
                        this.startDate, this.endDate,
                        unit: displayChartAs);
                    Navigator.pop(context);
                    if (chartInfo == null) {
                      showDialog(
                          context: context,
                          builder: (context) {
                            return AlertDialog(
                              title: Text(
                                "Dã có lỗi xảy ra!",
                                style: TextStyle(
                                    fontSize: 18, fontWeight: FontWeight.bold),
                              ),
                              actions: [
                                ElevatedButton(
                                    onPressed: () {
                                      Navigator.pop(context);
                                    },
                                    child: Container(
                                      child: Text("Đóng"),
                                    ))
                              ],
                            );
                          });
                      return;
                    }
                      setState(() {
                        buildBarChartGroup(chartInfo!);
                      });
                  },
                ),
              ),
              chartInfo == null
                  ? Container(
                      height: 200,
                      child: Center(
                        child: Text(
                          "Không có dữ liệu",
                          style: TextStyle(
                              fontWeight: FontWeight.w300,
                              fontSize: 20,
                              color: Colors.grey),
                        ),
                      ),
                    )
                  : SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Container(
                      alignment: Alignment.center,
                      padding: EdgeInsets.all(8.0),
                      height: 500,
                      width: (chartInfo!.revenue.length+2)*40,
                      child: BarChart(BarChartData(
                        minY: 0,
                        alignment:BarChartAlignment.spaceEvenly,
                      barGroups: listBarData,
                        axisTitleData: FlAxisTitleData(
                          leftTitle: AxisTitle(
                            showTitle: true,
                            titleText: "VNĐ"
                          ),
                          bottomTitle: AxisTitle(
                            showTitle: true,
                            titleText:"Thời gian"
                          )
                        ),
                        gridData: FlGridData(
                          show: true,
                          drawVerticalLine: false,
                          drawHorizontalLine: true,
                          checkToShowHorizontalLine: (value){
                            // value=value + chartInfo!.minValue.abs();
                            return value%findInterval(chartInfo!.maxValue.toDouble())==0;
                          },
                          getDrawingHorizontalLine: (value){
                            if (value == 0) {
                              return FlLine(color: const Color(0xff363753), strokeWidth: 3);
                            }
                            return FlLine(
                              color: const Color(0xff2a2747),
                              strokeWidth: 0.8,
                            );
                          }
                          // horizontalInterval: chartInfo!.maxValue<=0?1:chartInfo!.maxValue/10
                        ),
                        titlesData: FlTitlesData(
                          show: true,
                          bottomTitles: SideTitles(
                            reservedSize: 40,
                            rotateAngle: 90,
                            showTitles: true,
                            getTextStyles: (value) => const TextStyle(
                                color: Color(0xff7589a2), fontWeight: FontWeight.bold, fontSize: 14),
                            getTitles: (value){
                              if(value==-1||value==chartInfo!.revenue.length){
                                return "";
                              }
                              if(chartInfo!.unit=="day"){
                                return DateFormat("dd-MM").format(chartInfo!.fromFate.add(Duration(days: value.toInt())));
                              }
                              if(chartInfo!.unit=="month"){
                                return DateFormat("MM").format(DateTime(chartInfo!.fromFate.year,chartInfo!.fromFate.month+value.toInt(),chartInfo!.fromFate.day));
                              }
                              if(chartInfo!.unit=="year"){
                                return DateFormat("yyyy").format(DateTime(chartInfo!.fromFate.year+value.toInt(),chartInfo!.fromFate.month,chartInfo!.fromFate.day));
                              }
                              return "";
                            }
                          ),
                          leftTitles: SideTitles(
                            reservedSize: 30,
                            showTitles: true,
                            margin: 20,
                            interval: chartInfo!.maxValue<=0.toDouble()?1:findInterval(chartInfo!.maxValue.toDouble()),
                          )
                        )),
                        ),
                    ),
                  ),
              Container(
                child: Center(
                  child: Row(
                    children: [
                      Expanded(
                        flex: 2,
                        child: Text("Doanh \nthu:"),
                      ),
                      Expanded(
                        flex: 1,
                        child: Center(
                          child: Container(
                            height: 10,
                            width: 10,
                            color: Colors.green,
                          ),
                        ),
                      ),
                      Expanded(flex: 1,child: Container(),),
                      Expanded(
                        flex: 2,
                        child: Text("Tiền \nnhập hàng:"),
                      ),
                      Expanded(
                        flex: 1,
                        child: Center(
                          child: Container(
                            height: 10,
                            width: 10,
                            color: Colors.blue,
                          ),
                        ),
                      ),
                      Expanded(flex: 1,child: Container(),),
                      Expanded(
                        flex: 2,
                        child: Text("Lợi \nnhuận:"),
                      ),
                      Expanded(
                        flex: 1,
                        child: Center(
                          child: Container(
                            height: 10,
                            width: 10,
                            color: Colors.red,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              chartInfo==null?Container():
              Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  SizedBox(height: 30,),
                  Center(child: Text("Chi tiết doanh thu :",style: TextStyle(fontSize: 16,fontWeight: FontWeight.bold),),),
                  SizedBox(height: 10,),
                  ListView.separated(physics: NeverScrollableScrollPhysics(),shrinkWrap:true,itemBuilder: (context,index){
                    return Row(
                      children: [
                        Expanded(flex:1,child: Text(DateFormat("dd-MM-yyyy").format(startDate.add(Duration(days: index))))),
                        Expanded(child: Container(alignment:Alignment.centerRight,child: Text(NumberFormat().format(chartInfo!.revenue[index])+" VNĐ")))
                      ],
                    );
                  }, separatorBuilder: (context,index)=>Divider(), itemCount: chartInfo!.revenue.length),
                  SizedBox(height: 30,),
                  Center(child: Text("Chi tiết tiền nhập hàng :",style: TextStyle(fontSize: 16,fontWeight: FontWeight.bold),),),
                  SizedBox(height: 10,),
                  ListView.separated(physics: NeverScrollableScrollPhysics(),shrinkWrap:true,itemBuilder: (context,index){
                    return Row(
                      children: [
                        Expanded(flex:1,child: Text(DateFormat("dd-MM-yyyy").format(startDate.add(Duration(days: index))))),
                        Expanded(child: Container(alignment: Alignment.centerRight,child: Text(NumberFormat().format(chartInfo!.purchase[index])+" VNĐ",)))
                      ],
                    );
                  }, separatorBuilder: (context,index)=>Divider(), itemCount: chartInfo!.purchase.length),
                  SizedBox(height: 30,),
                  Center(child: Text("Chi tiết lợi nhuận :",style: TextStyle(fontSize: 16,fontWeight: FontWeight.bold),),),
                  SizedBox(height: 10,),
                  ListView.separated(physics: NeverScrollableScrollPhysics(),shrinkWrap:true,itemBuilder: (context,index){
                    return Row(
                      children: [
                        Expanded(flex:1,child: Text(DateFormat("dd-MM-yyyy").format(startDate.add(Duration(days: index))))),
                        Expanded(child: Container(alignment:Alignment.centerRight,child: Text(NumberFormat().format(chartInfo!.profit[index])+" VNĐ")))
                      ],
                    );
                  }, separatorBuilder: (context,index)=>Divider(), itemCount: chartInfo!.profit.length),
                ],
              )
            ],
          ),
        ),
      ),
    );
  }
}
