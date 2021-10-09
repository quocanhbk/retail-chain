import 'package:bkrm/pages/managementModule/detailReport/categoryReportPage.dart';
import 'package:bkrm/pages/managementModule/detailReport/chartPage.dart';
import 'package:bkrm/pages/managementModule/detailReport/customerReportPage.dart';
import 'package:bkrm/pages/managementModule/detailReport/itemReportPage.dart';
import 'package:bkrm/pages/managementModule/detailReport/supplierReportPage.dart';
import 'package:flutter/material.dart';
import 'package:page_transition/page_transition.dart';

import 'package:bkrm/pages/Nav2App.dart';

class ListDetailReport extends StatefulWidget {
  @override
  _ListDetailReportState createState() => _ListDetailReportState();
}

class _ListDetailReportState extends State<ListDetailReport> {
  List<String> reportList = [
    "Thống kê về doanh thu - lợi nhuận - tiền nhập hàng",
    "Thống kê về sản phẩm",
    "Thống kê về danh mục",
    "Thống kê về khách hàng",
    "Thống kê về nhà cung cấp"
  ];
  List<Widget> reportListWidget =[
    ChartPage(),
    ItemReportPage(),
    CategoryReportPage(),
    CustomerReportPage(),
    SupplierReportPage()
  ];
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text("Thống kê chi tiết",),),
      body: Container(
        child: ListView.separated(itemCount: reportList.length,itemBuilder: (context,index){
          return ListTile(
            title: Text(reportList[index]),
            onTap: (){
              Navigator.push(context, PageTransition(child: reportListWidget[index], type: pageTransitionType));
            },
          );
        }, separatorBuilder: (BuildContext context, int index) { return Divider(); },)
      ),
    );
  }
}
