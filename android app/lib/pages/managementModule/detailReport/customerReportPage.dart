import 'package:bkrm/services/info/report/customerReportInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:flutter/material.dart';
import 'package:flutter_datetime_picker/flutter_datetime_picker.dart';
import 'package:intl/intl.dart';

class CustomerReportPage extends StatefulWidget {
  @override
  _CustomerReportPageState createState() => _CustomerReportPageState();
}

class _CustomerReportPageState extends State<CustomerReportPage> {
  String displayChartAs = "day";
  DateTime startDate = DateTime.now().subtract(Duration(days: 7));
  DateTime endDate = DateTime.now();
  CustomerReportInfo? customerReportInfo;

  bool selectDayManual = false;

  TextEditingController startDateController = TextEditingController();
  TextEditingController endDateController = TextEditingController();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text("Thống kê khách hàng"),),
      body: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(8.0),
          child: Column(
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
                    customerReportInfo = await BkrmService().getReportCustomer(
                        fromDate: startDate, toDate: endDate);
                    if(customerReportInfo!=null){
                      if(customerReportInfo!.listCustomer.isEmpty){
                        customerReportInfo=null;
                      }
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
              customerReportInfo==null?Container(
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
              ):
                  Column(
                    children: [
                      Text("Thống kê khách hàng theo doanh thu: ",style: TextStyle(fontSize: 16,fontWeight: FontWeight.bold),),
                      Padding(
                        padding: const EdgeInsets.all(8.0),
                        child: ListView.separated(physics: NeverScrollableScrollPhysics(),shrinkWrap: true,itemBuilder: (context,index){
                          return Row(
                            children: [
                              Expanded(flex: 2,child: Text(customerReportInfo!.listCustomer[index].name),),
                              Expanded(flex: 1,child: Text(NumberFormat().format(customerReportInfo!.listCustomer[index].totalBuyPrice)+" VNĐ",))
                            ],
                          );
                        }, separatorBuilder: (context,index)=>Divider(), itemCount: customerReportInfo!.listCustomer.length),
                      )
                    ],
                  )
            ],
          ),
        ),
      ),
    );
  }
}
