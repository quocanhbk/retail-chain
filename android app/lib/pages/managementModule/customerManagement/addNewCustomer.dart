import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/menuWidget.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter_datetime_picker/flutter_datetime_picker.dart';
import 'package:intl/intl.dart';

class AddNewCustomerPage extends StatefulWidget {
  String? referName;
  String? referPhoneNumber;

  AddNewCustomerPage({String ?referName, String? referPhoneNumber}){
    this.referName=referName;
    this.referPhoneNumber=referPhoneNumber;
  }

  @override
  _AddNewCustomerPageState createState() => _AddNewCustomerPageState();
}

class _AddNewCustomerPageState extends State<AddNewCustomerPage> {
  final _formKey = GlobalKey<FormState>();

  bool needRefresh = false;

  TextEditingController phoneNumberController = TextEditingController();
  TextEditingController nameController = TextEditingController();
  TextEditingController addressController = TextEditingController();
  TextEditingController emailController = TextEditingController();
  TextEditingController dateOfBirthController = TextEditingController();

  String? genderValue;
  DateTime? dateOfBirth;

  bool phoneNumberValid=false;
  bool emailValid = false;


  @override
  void initState() {
    setUpReferInfo();
    super.initState();
  }

  setUpReferInfo(){
    if(widget.referName!=null){
      nameController.text=widget.referName!;
    }
    if(widget.referPhoneNumber!=null){
      phoneNumberController.text=widget.referPhoneNumber!;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      resizeToAvoidBottomInset: true,
      appBar: AppBar(
        title: Text("Tạo khách hàng mới"),
      ),
      body: WillPopScope(
        onWillPop: ()async{
          Navigator.pop(context,needRefresh);
          return needRefresh;
        },
        child: SingleChildScrollView(
          child: Container(
            padding: EdgeInsets.all(8.0),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  SizedBox(
                    height: 30,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Số điện thoại: ",
                            style:
                            TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          keyboardType: TextInputType.phone,
                          controller: phoneNumberController,
                          autovalidateMode: AutovalidateMode.always,
                          validator: (phoneNumber){
                            if(phoneNumber==null||phoneNumber==""){
                              phoneNumberValid=false;
                              return " *Bắt buộc";
                            }else{
                              phoneNumberValid=true;
                              return null;
                            }
                          },
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Họ tên: ",
                            style:
                            TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          controller: nameController,
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Địa chỉ : ",
                            style:
                            TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          controller: addressController,
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Email : ",
                            style:
                            TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          autovalidateMode: AutovalidateMode.always,
                          validator: (email){
                            if(email==null||email==""){
                              emailValid=true;
                              return null;
                            }
                            if (RegExp(
                                r"^[a-zA-Z0-9.a-zA-Z0-9.!#$%&'*+-/=?^_`{|}~]+@[a-zA-Z0-9]+\.[a-zA-Z]+")
                                .hasMatch(email)) {
                              emailValid = true;
                              return null;
                            }else{
                              emailValid = false;
                              return " * Email không hợp lệ";
                            }
                          },
                          controller: emailController,
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Text(
                        "Giới tính: ",
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
                      ),
                      Radio(
                          value: "male",
                          groupValue: genderValue,
                          onChanged: (dynamic value) {
                            genderValue = value;
                            setState(() {});
                          }),
                      Text(
                        "Nam",
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                      ),
                      Radio(
                          value: "female",
                          groupValue: genderValue,
                          onChanged: (dynamic value) {
                            genderValue = value;
                            setState(() {});
                          }),
                      Text(
                        "Nữ",
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                      ),
                    ],
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Ngày sinh: ",
                            style:
                            TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: InkWell(
                          onTap: () {
                            DatePicker.showDatePicker(context,
                                locale: LocaleType.vi,
                                currentTime: DateTime.now(),
                                maxTime: DateTime.now(), onConfirm: (date) {
                                  dateOfBirth = date;
                                  dateOfBirthController.text =
                                      DateFormat("dd-MM-yyyy").format(date);
                                  setState(() {});
                                });
                          },
                          child: IgnorePointer(
                            child: TextFormField(
                              readOnly: true,
                              controller: dateOfBirthController,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  SizedBox(height: 30,),
                  Center(child: RaisedButton(padding: EdgeInsets.all(16.0),color: Colors.blue,onPressed: ()async{
                    _formKey.currentState!.validate();
                    if(phoneNumberValid&&emailValid){
                      showDialog(context: context,builder: (context){
                        return AlertDialog(
                          content: Container(
                            height: 60,
                            child: Center(child: CircularProgressIndicator(),),
                          ),
                        );
                      });
                      MsgInfoCode? returnCode = await BkrmService().createCustomer(phoneNumber: phoneNumberController.value.text,name: nameController.value.text,address: addressController.value.text==""?null:addressController.value.text,
                      email: emailController.value.text==""?null:emailController.value.text,gender: genderValue,dateOfBirth: dateOfBirth);
                      Navigator.pop(context);
                      if(returnCode==MsgInfoCode.signUpSuccess){
                        showDialog(context: context,builder: (context){
                          return AlertDialog(
                            title: Text("Tạo khách hàng mới thành công."),
                            actions: [
                              FlatButton(onPressed: (){
                                needRefresh=true;
                                Navigator.pop(context);
                                Navigator.pop(context,needRefresh);
                              }, child: Text("Đóng"))
                            ],
                          );
                        });
                        return;
                      }else{
                        if(returnCode==MsgInfoCode.phoneNumberAlreadyBeenTaken){
                          showDialog(context: context,builder: (context){
                            return AlertDialog(
                              title: Text("Số điện thoại đã tồn tại."),
                              actions: [
                                FlatButton(onPressed: (){
                                  Navigator.pop(context);
                                }, child: Text("Đóng"))
                              ],
                            );
                          });
                          return;
                        }
                        showDialog(context: context,builder: (context){
                          return AlertDialog(
                            title: Text("Tạo khách hàng thất bại."),
                            actions: [
                              FlatButton(onPressed: (){
                                Navigator.pop(context);
                              }, child: Text("Đóng"))
                            ],
                          );
                        });
                      }
                    }
                  },child: Text("Thêm khách hàng",style: TextStyle(color:Colors.white,fontSize: 20),),))
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
