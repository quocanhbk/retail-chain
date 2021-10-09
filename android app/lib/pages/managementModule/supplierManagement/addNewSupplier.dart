import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/menuWidget.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter/widgets.dart';

class AddNewSupplierPage extends StatefulWidget {
  @override
  _AddNewSupplierPageState createState() => _AddNewSupplierPageState();
}

class _AddNewSupplierPageState extends State<AddNewSupplierPage> {
  final _formKey = GlobalKey<FormState>();

  bool phoneNumberValid = false;
  bool emailValid = false;
  bool nameValid = false;

  TextEditingController phoneNumberController = TextEditingController();
  TextEditingController nameController = TextEditingController();
  TextEditingController addressController = TextEditingController();
  TextEditingController emailController = TextEditingController();
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      resizeToAvoidBottomInset: true,
      appBar: AppBar(title: Text("Thêm nhà cung cấp"),),
      body:
        SingleChildScrollView(
          child: Container(
            padding: EdgeInsets.all(8.0),
            child: Form(
              key: _formKey,
              child: Column(
                children: [
                  SizedBox(height: 30,),
                  Row(
                    children: [
                      Expanded(flex:1,child: Text("Số điện thoại: ",style: TextStyle(fontSize: 14,fontWeight: FontWeight.w400),)),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          keyboardType: TextInputType.phone,
                          controller: phoneNumberController,
                          autovalidateMode: AutovalidateMode.always,
                          validator: (name){
                            if(name==null||name==""){
                              phoneNumberValid=false;
                              return " * Bắt buộc";
                            }else{
                              phoneNumberValid=true;
                              return null;
                            }
                          },
                        ),
                      )
                    ],
                  ),
                  SizedBox(height: 10,),
                  Row(
                    children: [
                      Expanded(flex:1,child: Text("Họ tên: ",style: TextStyle(fontSize: 14,fontWeight: FontWeight.w400),)),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          controller: nameController,
                          autovalidateMode: AutovalidateMode.always,
                          validator: (name){
                            if(name==null||name==""){
                              nameValid=false;
                              return " * Bắt buộc";
                            }else{
                              nameValid =true;
                              return null;
                            }
                          },
                        ),
                      )
                    ],
                  ),
                  SizedBox(height: 10,),
                  Row(
                    children: [
                      Expanded(flex:1,child: Text("Email: ",style: TextStyle(fontSize: 14,fontWeight: FontWeight.w400),)),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          controller: emailController,
                          autovalidateMode: AutovalidateMode.always,
                          validator: (email){
                            if(email==""||email==null){
                              emailValid=true;
                              return null;
                            }
                            if (RegExp(
                                r"^[a-zA-Z0-9.a-zA-Z0-9.!#$%&'*+-/=?^_`{|}~]+@[a-zA-Z0-9]+\.[a-zA-Z]+")
                                .hasMatch(email)) {
                              emailValid = true;
                              return null;
                            } else {
                              emailValid = false;
                              return "Email không hợp lệ";
                            }
                          },
                        ),
                      )
                    ],
                  ),
                  SizedBox(height: 10,),
                  Row(
                    children: [
                      Expanded(flex:1,child: Text("Địa chỉ: ",style: TextStyle(fontSize: 14,fontWeight: FontWeight.w400),)),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          controller: addressController,
                          autovalidateMode: AutovalidateMode.always,
                        ),
                      )
                    ],
                  ),
                  SizedBox(height: 30,),
                  Center(child: RaisedButton(padding: EdgeInsets.all(16.0),color: Colors.blue,onPressed: ()async{
                    debugPrint("nameValid");
                    debugPrint(phoneNumberValid.toString());
                    _formKey.currentState!.validate();
                    if(phoneNumberValid&&nameValid&&emailValid){
                      showDialog(context: context,builder: (context){
                        return AlertDialog(
                          content: Container(
                            height: 60,
                            child: Center(child: CircularProgressIndicator(),),
                          ),
                        );
                      });
                      MsgInfoCode? returnCode = await BkrmService().createSupplier(name: nameController.value.text, email: emailController.value.text==""?null:emailController.value.text
                      ,phoneNumber: phoneNumberController.value.text,address: addressController.value.text==""?null:addressController.value.text);
                      Navigator.pop(context);
                      if(returnCode==MsgInfoCode.signUpSuccess){
                        showDialog(context: context,builder: (context){
                          return AlertDialog(
                            title: Text("Tạo nhà cung cấp thành công"),
                            actions: [
                              FlatButton(onPressed: (){
                                Navigator.pop(context);
                                Navigator.pop(context,"created");
                              }, child: Text("Đóng"))
                            ],
                          );
                        });
                        return;
                      }else{
                        if(returnCode==MsgInfoCode.usernameAlreadyBeenTaken){
                          showDialog(context: context,builder: (context){
                            return AlertDialog(
                              title: Text("Tên nhà cung cấp đã tồn tại"),
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
                            title: Text("Tạo nhà cung cấp thất bại"),
                            actions: [
                              FlatButton(onPressed: (){
                                Navigator.pop(context);
                              }, child: Text("Đóng"))
                            ],
                          );
                        });
                      }
                    }
                  },child: Text("Đăng ký",style: TextStyle(color:Colors.white,fontSize: 20),),))
                ],
              ),
            ),
          ),
        )
    );
  }
}
