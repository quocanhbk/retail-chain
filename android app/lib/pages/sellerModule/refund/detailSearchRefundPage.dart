import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter_datetime_picker/flutter_datetime_picker.dart';
import 'package:intl/intl.dart';

import 'package:bkrm/pages/sellerModule/refund//listDetailSearch.dart';
import 'package:page_transition/page_transition.dart';

import 'package:bkrm/pages/Nav2App.dart';

class DetailSearchRefundPage extends StatefulWidget {
  @override
  _DetailSearchRefundPageState createState() =>
      _DetailSearchRefundPageState();
}

class _DetailSearchRefundPageState
    extends State<DetailSearchRefundPage> {
  TextEditingController idController=TextEditingController();
  TextEditingController searchQueryController=TextEditingController();
  TextEditingController totalMoneyFrom=TextEditingController();
  TextEditingController totalMoneyTo=TextEditingController();
  DateTime? dateFrom;
  DateTime? dateTo;

  bool totalMoneyValid = true;
  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        FocusScope.of(context).requestFocus(FocusNode());
      },
      child: Scaffold(
        resizeToAvoidBottomInset: true,
          appBar: AppBar(
            title: Text("Tìm đơn trả hàng "),
          ),
          body: SingleChildScrollView(
            child: Padding(
              padding: const EdgeInsets.all(8.0),
              child: Column(
                children: [
                  Container(
                    height: 10,
                  ),
                  Padding(
                    padding: const EdgeInsets.all(8.0),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Expanded(
                            flex: 1,
                            child: Text(
                              "Mã đơn trả hàng : ",                              style: TextStyle(
                                  fontWeight: FontWeight.w400, fontSize: 16),
                            )),
                        Expanded(flex: 3, child: TextField(
                          controller: idController,
                        ))
                      ],
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.all(8.0),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Expanded(
                            flex: 1,
                            child: Text(
                              "Từ khóa: ",
                              style: TextStyle(

                                  fontWeight: FontWeight.w400, fontSize: 16),
                            )),
                        Expanded(flex: 3, child: TextField(
                          decoration: InputDecoration(
                            hintText: "Tên khách hàng hoặc số điện thoại"
                          ),
                          controller: searchQueryController,
                        ))
                      ],
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.all(8.0),
                    child: Container(
                        alignment: Alignment.centerLeft,
                        child: Text(
                          "Tổng tiền trả: ",
                          style: TextStyle(
                              fontWeight: FontWeight.w400, fontSize: 16),
                        )),
                  ),
                  Padding(
                    padding: const EdgeInsets.all(8.0),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Expanded(
                            flex: 1,
                            child: Text(
                              "Từ : ",
                              style: TextStyle(
                                  fontWeight: FontWeight.w400, fontSize: 16),
                            )),
                        Expanded(
                            flex: 3,
                            child: TextFormField(
                              controller: totalMoneyFrom,
                              keyboardType: TextInputType.number,
                              autovalidateMode: AutovalidateMode.always,
                              validator: (moneyFrom){
                                int? totalMoneyTo = int.tryParse(this.totalMoneyTo.value.text);
                                int? totalMoneyFrom = int.tryParse(moneyFrom??"");
                                if(totalMoneyTo!=null&&totalMoneyFrom!=null){
                                  if(totalMoneyFrom>totalMoneyTo){
                                    totalMoneyValid=false;
                                    return "Nhỏ hơn \"Đến:\" ";
                                  }
                                }
                                totalMoneyValid=true;
                                return null;
                              },
                            )),
                        Expanded(
                            flex: 1,
                            child: Text(
                              "Đến : ",
                              style: TextStyle(
                                  fontWeight: FontWeight.w400, fontSize: 16),
                            )),
                        Expanded(
                            flex: 3,
                            child: TextFormField(
                              controller: totalMoneyTo,
                              keyboardType: TextInputType.number,
                              autovalidateMode: AutovalidateMode.always,
                              validator: (moneyTo){
                                int? totalMoneyFrom = int.tryParse(this.totalMoneyFrom.value.text);
                                int? totalMoneyTo = int.tryParse(moneyTo??"");
                                if(totalMoneyTo!=null&&totalMoneyFrom!=null){
                                  if(totalMoneyFrom>totalMoneyTo){
                                    totalMoneyValid=false;
                                    return " Lớn hơn \"Từ:\" ";
                                  }
                                }
                                totalMoneyValid=true;
                                return null;
                              },
                            ))
                      ],
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.all(8.0),
                    child: Container(
                        alignment: Alignment.centerLeft,
                        child: Text(
                          "Ngày trả : ",
                          style: TextStyle(
                              fontWeight: FontWeight.w400, fontSize: 16),
                        )),
                  ),
                  Padding(
                    padding: const EdgeInsets.all(8.0),
                    child: Row(
                      children: [
                        Expanded(
                            flex: 1,
                            child: Text(
                              "Từ : ",
                              style: TextStyle(
                                  fontWeight: FontWeight.w400, fontSize: 16),
                            )),
                        Expanded(
                          flex: 3,
                          child: ButtonTheme(
                            child: Row(children: [
                              Expanded(
                                child: OutlineButton(
                                    borderSide: BorderSide(width: 1.0),
                                    color: Colors.grey,
                                    onPressed: () {
                                      DatePicker.showDatePicker(context,
                                          currentTime: dateFrom == null
                                              ? DateTime.now()
                                              : dateFrom,
                                          maxTime: dateTo??DateTime.now(),
                                          onConfirm: (date) {
                                            dateFrom = date;
                                            setState(() {});
                                          }, locale: LocaleType.vi);
                                    },
                                    child: Text(
                                      dateFrom != null
                                          ? DateFormat("dd-MM-yyyy")
                                          .format(dateFrom!)
                                          : "",
                                      style: TextStyle(
                                          fontSize: 14,
                                          fontWeight: FontWeight.bold),
                                    )),
                              ),
                              IconButton(
                                  icon: Icon(Icons.close),
                                  onPressed: () {
                                    dateFrom = null;
                                    setState(() {});
                                  })
                            ]),
                          ),
                        ),
                      ],
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.all(8.0),
                    child: Row(
                      children: [
                        Expanded(
                            flex: 1,
                            child: Text(
                              "Đến : ",
                              style: TextStyle(
                                  fontWeight: FontWeight.w400, fontSize: 16),
                            )),
                        Expanded(
                          flex: 3,
                          child: ButtonTheme(
                            child: Row(children: [
                              Expanded(
                                child: OutlineButton(
                                    borderSide: BorderSide(width: 1.0),
                                    color: Colors.grey,
                                    onPressed: () {
                                      DatePicker.showDatePicker(context,
                                          currentTime: dateTo == null
                                              ? DateTime.now()
                                              : dateTo,
                                          minTime: dateFrom,
                                          maxTime: DateTime.now(),
                                          onConfirm: (date) {
                                            dateTo = date;
                                            setState(() {});
                                          }, locale: LocaleType.vi);
                                    },
                                    child: Text(
                                      dateTo != null
                                          ? DateFormat("dd-MM-yyyy").format(dateTo!)
                                          : "",
                                      style: TextStyle(
                                          fontSize: 14,
                                          fontWeight: FontWeight.bold),
                                    )),
                              ),
                              IconButton(
                                  icon: Icon(Icons.close),
                                  onPressed: () {
                                    dateTo = null;
                                    setState(() {});
                                  })
                            ]),
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(height: 30,),
                  Padding(
                      padding: const EdgeInsets.all(8.0),
                      child: Center(
                        child: FlatButton(

                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.all(Radius.circular(10.0))),
                          color: Colors.blueAccent,
                          onPressed: () {
                            if(!totalMoneyValid){
                              return;
                            }
                            int? totalMoneyFrom = int.tryParse(this.totalMoneyFrom.value.text);
                            int? totalMoneyTo = int.tryParse(this.totalMoneyTo.value.text);
                            if(dateFrom!=null&&dateTo!=null){
                              if(dateFrom!.isAfter(dateTo!)){
                                DateTime temp = dateFrom!;
                                dateFrom=dateTo;
                                dateTo=temp;
                              }
                            }
                            Navigator.push(context, PageTransition(child: ListDetailSearch(searchId: int.tryParse(idController.value.text),searchQuery: searchQueryController.value.text,
                              totalMoneyFrom: totalMoneyFrom,totalMoneyTo: totalMoneyTo,
                              dateTimeFrom: dateFrom,dateTimeTo: dateTo,), type: pageTransitionType));
                          },
                          child: Container(
                              height: 50,
                              width: 100,
                              child: Center(
                                  child: Text(
                                "Tìm kiếm",
                                style: TextStyle(
                                    fontSize: 20, fontWeight: FontWeight.w600),
                              ))),
                        ),
                      )),
                ],
              ),
            ),
          )),
    );
  }
}
