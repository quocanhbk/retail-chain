import 'package:bkrm/pages/managementModule/detailReport/listDetailReport.dart';
import 'package:bkrm/services/info/managementInfo/dashboardInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/menuWidget.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';
import 'package:flutter_staggered_grid_view/flutter_staggered_grid_view.dart';
import 'package:intl/intl.dart';
import 'package:page_transition/page_transition.dart';

import 'package:bkrm/pages/Nav2App.dart';

// import 'shop_items_page.dart';
class ItemDisplayStore {
  const ItemDisplayStore(this.name);
  final String name;
}

List<ItemDisplayStore> stores = <ItemDisplayStore>[];

class DashboardPage extends StatefulWidget {
  @override
  _DashboardPageState createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {

  BkrmService bkrmService = BkrmService();
  List<DashboardInfo> dashboardInfos = <DashboardInfo>[];
  DashboardInfo? currentDashboardInfo;
  int revenueLastWeek = 0;
  int? numberEmployee = 0;
  int? numberGood = 0;
  int? costOfImportedGoods = 0;
  String totalRevenueChart = "";
  List<int?> maxHeightChart = [0, 0, 0];
  Future<bool>? getData;
  NumberFormat formatter = NumberFormat();
  void initState() {
    stores = [];
    getData = getDashboardInfo();
    super.initState();
  }

  Future<bool> getDashboardInfo() async {
    currentDashboardInfo = await bkrmService.getDashboardInfo();
    numberEmployee = currentDashboardInfo!.numberEmployee;
    numberGood = currentDashboardInfo!.itemQuantities;
    costOfImportedGoods = currentDashboardInfo!.importFee;
    totalRevenueChart = currentDashboardInfo!.revenueLastWeek.toString()+" VNĐ";
    charts[0].clear();
    charts[1].clear();
    charts[2].clear();
    int i = 1;
    int? maxHeight = 0;
    for (int? data in currentDashboardInfo!.getRevenueWeekDayChart().reversed.toList()) {
      charts[0].add(FlSpot(i.toDouble(), data!.toDouble()));
      if (data > maxHeight!) maxHeight = data;
      i += 1;
    }
    maxHeightChart[0] = maxHeight;
    maxHeight = 0;
    i = 1;
    for (int? data in currentDashboardInfo!.getRevenueMonthDayChart().reversed.toList()) {
      charts[1].add(FlSpot(i.toDouble(), data!.toDouble()));
      if (data > maxHeight!) maxHeight = data;
      i += 1;
    }
    maxHeightChart[1] = maxHeight;
    maxHeight = 0;
    i = 1;
    for (int? data in currentDashboardInfo!.getRevenueYearDayChart().reversed.toList()) {
      charts[2].add(FlSpot(i.toDouble(), data!.toDouble()));
      if (data > maxHeight!) maxHeight = data;
      i += 1;
    }
    maxHeightChart[2] = maxHeight;
    revenueLastWeek = currentDashboardInfo!.revenueLastWeek;
    return true;
  }

  final List<List<FlSpot>> charts = [
    [
      FlSpot(1, 1),
      FlSpot(2, 1),
      FlSpot(3, 1),
      FlSpot(4, 1),
      FlSpot(5, 1),
      FlSpot(6, 1),
      FlSpot(7, 1),
    ],
    [
      FlSpot(1, 1),
      FlSpot(2, 1),
      FlSpot(3, 1),
      FlSpot(4, 1),
      FlSpot(5, 1),
      FlSpot(6, 1),
      FlSpot(7, 1),
    ],
    [
      FlSpot(1, 1),
      FlSpot(2, 1),
      FlSpot(3, 1),
      FlSpot(4, 1),
      FlSpot(5, 1),
      FlSpot(6, 1),
      FlSpot(7, 1),
    ],
  ];

  static final List<String?> chartDropdownItems = [
    '7 ngày gần nhất',
    '30 ngày gần nhất',
    '365 ngày gần nhất'
  ];
  String? actualDropdown = chartDropdownItems[0];
  int actualChart = 0;

  @override
  Widget build(BuildContext context) {
    return FutureBuilder(
        future: getData,
        builder: (context, snapshot) {
          if (!snapshot.hasData) {
            return Scaffold(
                drawer: ExpansionDrawer(this.context),
                appBar: AppBar(
                  title: Text('Thống kê',
                      style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w700,
                          fontSize: 30.0)),
                ),
                body: Container(
                  child: Center(
                      child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                        Center(
                          child: CircularProgressIndicator(),
                        ),
                        Container(
                          height: 30,
                        ),
                        Text(
                          "Đang lấy dữ liệu từ hệ thống...",
                          style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: Colors.grey),
                        )
                      ])),
                ));
          } else {
            if (snapshot.hasError) {
              return Scaffold(
                  drawer: ExpansionDrawer(this.context),
                  appBar: AppBar(
                    title: Text('Thống kê',
                        style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w700,
                            fontSize: 30.0)),
                  ),
                  body: Container(
                    child: Text(
                      "Đã có lỗi xảy ra!!!",
                      style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: Colors.grey),
                    ),
                  ));
            }
            return Scaffold(
                drawer: ExpansionDrawer(this.context),
                appBar: AppBar(
                  title: Text('Thống kê',
                      style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w700,
                          fontSize: 30.0)),
                ),
                body: RefreshIndicator(
                  onRefresh: () async {
                    await getDashboardInfo();
                    return;
                  },
                  child: StaggeredGridView.count(
                    crossAxisCount: 2,
                    crossAxisSpacing: 12.0,
                    mainAxisSpacing: 12.0,
                    padding:
                        EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
                    children: <Widget>[
                      _buildTile(
                        Padding(
                          padding: const EdgeInsets.all(24.0),
                          child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              crossAxisAlignment: CrossAxisAlignment.center,
                              children: <Widget>[
                                Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: <Widget>[
                                    Text('Doanh Thu',
                                        style: TextStyle(
                                            color: Colors.blueAccent)),
                                    Text(formatter.format(revenueLastWeek)+ " VNĐ",
                                        style: TextStyle(
                                            color: Colors.black,
                                            fontWeight: FontWeight.w700,
                                            fontSize: MediaQuery.of(context).size.width<MediaQuery.of(context).size.height?MediaQuery.of(context).size.width*0.08:MediaQuery.of(context).size.height*0.08))
                                  ],
                                ),
                                Material(
                                    color: Colors.blue,
                                    borderRadius: BorderRadius.circular(24.0),
                                    child: Center(
                                        child: Padding(
                                      padding: const EdgeInsets.all(16.0),
                                      child: Icon(Icons.timeline,
                                          color: Colors.white, size: 30.0),
                                    )))
                              ]),
                        ),
                      ),
                      _buildTile(
                        Padding(
                            padding: const EdgeInsets.all(24.0),
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              mainAxisAlignment: MainAxisAlignment.start,
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: <Widget>[
                                Row(
                                  mainAxisAlignment:
                                      MainAxisAlignment.spaceBetween,
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: <Widget>[
                                    Column(
                                      mainAxisAlignment:
                                          MainAxisAlignment.start,
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: <Widget>[
                                        Text(actualDropdown!,
                                            style:
                                                TextStyle(color: Colors.green)),
                                        Text(totalRevenueChart,
                                            style: TextStyle(
                                                color: Colors.black,
                                                fontWeight: FontWeight.w700,
                                                fontSize: 20.0)),
                                      ],
                                    ),
                                    DropdownButton(
                                        isDense: true,
                                        value: actualDropdown,
                                        onChanged: (String? value) =>
                                            setState(() {
                                              actualDropdown = value;
                                              actualChart = chartDropdownItems
                                                  .indexOf(value);
                                              switch (actualChart) {
                                                case 0:
                                                  totalRevenueChart =
                                                      formatter.format(currentDashboardInfo!
                                                          .revenueLastWeek)
                                                          +" VNĐ";
                                                  break;
                                                case 1:
                                                  totalRevenueChart =
                                                      formatter.format(currentDashboardInfo!
                                                          .revenueLastMonth)
                                                          +" VNĐ";
                                                  break;
                                                case 2:
                                                  totalRevenueChart =
                                                      formatter.format(currentDashboardInfo!
                                                          .revenueLastYear)
                                                          +" VNĐ";
                                                  break;
                                                default:
                                                  totalRevenueChart =
                                                      formatter.format(currentDashboardInfo!
                                                          .revenueLastWeek)+" VNĐ";
                                                          ;
                                                // Refresh the chart
                                              }
                                            }),
                                        items: chartDropdownItems
                                            .map((String? title) {
                                          return DropdownMenuItem(
                                            value: title,
                                            child: Text(title!,
                                                style: TextStyle(
                                                    color: Colors.blue,
                                                    fontWeight: FontWeight.w400,
                                                    fontSize: 14.0)),
                                          );
                                        }).toList())
                                  ],
                                ),
                                Padding(padding: EdgeInsets.only(bottom: 12.0)),
                                Container(
                                  height: 320,
                                  child: LineChart(
                                    LineChartData(
                                        axisTitleData: FlAxisTitleData(
                                            show: true,
                                            rightTitle: AxisTitle(
                                              reservedSize: MediaQuery.of(context).size.width<MediaQuery.of(context).size.height?MediaQuery.of(context).size.width*0.02:MediaQuery.of(context).size.height*0.02,
                                                margin: MediaQuery.of(context).size.width<MediaQuery.of(context).size.height?MediaQuery.of(context).size.width*0.02:MediaQuery.of(context).size.height*0.02,
                                                showTitle: true,
                                                titleText: "VNĐ"),
                                            bottomTitle: AxisTitle(
                                              margin: 20,
                                                showTitle: true,
                                                titleText:
                                                    charts[actualChart].length ==
                                                            12
                                                        ? "Tháng"
                                                        : "Ngày")),
                                        titlesData: FlTitlesData(
                                            bottomTitles: SideTitles(
                                              rotateAngle: 90,
                                                showTitles: true,
                                                interval:
                                                    charts[actualChart].length ==
                                                            30
                                                        ? 3
                                                        : 1,
                                                getTitles: (value) {
                                                  DateFormat formartter =
                                                      DateFormat("dd-MM");
                                                  var today = DateTime.now();
                                                  if (charts[actualChart]
                                                          .length ==
                                                      7) {
                                                    return formartter.format(
                                                        today.subtract(Duration(
                                                            days: 7 -
                                                                value.toInt())));
                                                  }
                                                  if (charts[actualChart]
                                                          .length ==
                                                      30) {
                                                    return formartter.format(
                                                        today.subtract(Duration(
                                                            days: 30 -
                                                                value.toInt())));
                                                  }
                                                  if (charts[actualChart]
                                                          .length ==
                                                      12) {
                                                    DateFormat formatMonth =
                                                        DateFormat("MM");
                                                    return formatMonth.format(
                                                        DateTime(
                                                            today.year,
                                                            today.month -
                                                                (12 -
                                                                    value
                                                                        .toInt()),
                                                            today.day));
                                                  }
                                                  return "";
                                                }),
                                            leftTitles: SideTitles(
                                              reservedSize: MediaQuery.of(context).size.width<MediaQuery.of(context).size.height?MediaQuery.of(context).size.width*0.07:MediaQuery.of(context).size.height*0.07,
                                              showTitles: true,
                                              interval:
                                                  maxHeightChart[actualChart] == 0
                                                      ? 1
                                                      : maxHeightChart[
                                                              actualChart]! /
                                                          10,
                                            )),
                                        gridData: FlGridData(
                                          show: true,
                                          verticalInterval:
                                              charts[actualChart].length == 30
                                                  ? 3
                                                  : 1,
                                          horizontalInterval:
                                              maxHeightChart[actualChart] == 0
                                                  ? 1
                                                  : maxHeightChart[actualChart]! /
                                                      10,
                                          drawVerticalLine: true,
                                          drawHorizontalLine: true,
                                        ),
                                        lineBarsData: [
                                          LineChartBarData(
                                            // belowBarData: BarAreaData(
                                            //   show: true,
                                            //   colors: [Color.fromARGB(225, 12, 64, 237),]
                                            // ),
                                            dotData: FlDotData(show: false),
                                            spots: charts[actualChart],
                                            isCurved: true,
                                            preventCurveOverShooting: true,
                                          )
                                        ]),
                                  ),
                                ),
                                Center(
                                  child: ElevatedButton(
                                    onPressed: () {
                                      Navigator.push(context,PageTransition(child: ListDetailReport(),type: pageTransitionType));
                                    },
                                    child: Container(color: Colors.blue,child: Text("Xem chi tiết",style: TextStyle(color: Colors.white),)),
                                  ),
                                )
                              ],
                            )),
                      ),
                      _buildTile(
                        Padding(
                          padding: const EdgeInsets.all(18.0),
                          child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              crossAxisAlignment: CrossAxisAlignment.center,
                              children: <Widget>[
                                Expanded(
                                  flex:2,
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: <Widget>[
                                      Text('Số lượng \nhàng:',
                                          style:
                                              TextStyle(color: Colors.redAccent)),
                                      Text(numberGood.toString(),
                                          style: TextStyle(
                                              color: Colors.black,
                                              fontWeight: FontWeight.w700,
                                              fontSize: MediaQuery.of(context).size.width<MediaQuery.of(context).size.height?MediaQuery.of(context).size.width*0.05:MediaQuery.of(context).size.height*0.05))
                                    ],
                                  ),
                                ),
                                Expanded(
                                  flex: 1,
                                  child: Material(
                                      color: Colors.red,
                                      borderRadius: BorderRadius.circular(24.0),
                                      child: Center(
                                          child: Icon(Icons.store,
                                              color: Colors.white, size: 30.0))),
                                )
                              ]),
                        ),
                      ),
                      _buildTile(
                        Padding(
                          padding: const EdgeInsets.all(18.0),
                          child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              crossAxisAlignment: CrossAxisAlignment.center,
                              children: <Widget>[
                                Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: <Widget>[
                                    Text('Nhân viên:',
                                        style: TextStyle(
                                            color: Colors.blueAccent,
                                            fontSize: MediaQuery.of(context).size.width<MediaQuery.of(context).size.height?MediaQuery.of(context).size.width*0.035:MediaQuery.of(context).size.height*0.035)),
                                    Text(numberEmployee.toString(),
                                        style: TextStyle(
                                            color: Colors.black,
                                            fontWeight: FontWeight.w700,
                                            fontSize: 25.0))
                                  ],
                                ),
                                Material(
                                    color: Colors.blue,
                                    borderRadius: BorderRadius.circular(24.0),
                                    child: Center(
                                        child: Padding(
                                      padding: EdgeInsets.all(16.0),
                                      child: Icon(Icons.attribution_outlined,
                                          color: Colors.white, size: 30.0),
                                    )))
                              ]),
                        ),
                      ),
                      _buildTile(
                        Padding(
                          padding: const EdgeInsets.all(18.0),
                          child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              crossAxisAlignment: CrossAxisAlignment.center,
                              children: <Widget>[
                                Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: <Widget>[
                                    Text('Chi phí nhập hàng trong 7 ngày:',
                                        style: TextStyle(
                                            color: Colors.orange,
                                            fontSize: MediaQuery.of(context).size.width<MediaQuery.of(context).size.height?MediaQuery.of(context).size.width*0.032:MediaQuery.of(context).size.height*0.032)),
                                    Text(formatter.format(costOfImportedGoods)+" VNĐ",
                                        style: TextStyle(
                                            color: Colors.black,
                                            fontWeight: FontWeight.w700,
                                            fontSize: MediaQuery.of(context).size.width<MediaQuery.of(context).size.height?MediaQuery.of(context).size.width*0.07:MediaQuery.of(context).size.height*0.07))
                                  ],
                                ),
                                Material(
                                    color: Colors.orange,
                                    borderRadius: BorderRadius.circular(24.0),
                                    child: Center(
                                        child: Padding(
                                      padding: EdgeInsets.all(16.0),
                                      child: Icon(Icons.inventory,
                                          color: Colors.white, size: 30.0),
                                    )))
                              ]),
                        ),
                      )
                    ],
                    staggeredTiles: [
                      StaggeredTile.extent(2, 110.0),
                      // StaggeredTile.extent(1, 180.0),
                      // StaggeredTile.extent(1, 180.0),
                      StaggeredTile.extent(2, 480.0),
                      StaggeredTile.extent(1, 110.0),
                      StaggeredTile.extent(1, 110.0),
                      StaggeredTile.extent(2, 110.0),
                    ],
                  ),
                ));
          }
        });
  }

  Widget _buildTile(Widget child, {Function()? onTap}) {
    return Material(
        elevation: 14.0,
        borderRadius: BorderRadius.circular(12.0),
        shadowColor: Color(0x802196F3),
        child: InkWell(
            // Do onTap() if it isn't null, otherwise do print()
            onTap: onTap != null
                ? () => onTap()
                : () {
                    print('Not set yet');
                  },
            child: child));
  }
}
