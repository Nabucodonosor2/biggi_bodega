-------------------- spu_plaza ---------------------------------
CREATE PROCEDURE [dbo].[spu_plaza](@ve_operacion varchar(20), @ve_cod_plaza numeric, @ve_nom_plaza varchar(100)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into plaza (cod_plaza,nom_plaza)
		values (@ve_cod_plaza,@ve_nom_plaza)
	end 
	if (@ve_operacion='UPDATE') begin
		update plaza 
		set nom_plaza = @ve_nom_plaza
	    where cod_plaza = @ve_cod_plaza
	end
	else if (@ve_operacion='DELETE') begin
		delete plaza 
    	where cod_plaza = @ve_cod_plaza
	end
END
go
