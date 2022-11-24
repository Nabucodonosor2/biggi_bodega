-------------------- spu_banco---------------------------------
CREATE PROCEDURE [dbo].[spu_banco](@ve_operacion varchar(20), @ve_cod_banco numeric, @ve_nom_banco varchar(100)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
	insert into banco (cod_banco,nom_banco)
	values (@ve_cod_banco,@ve_nom_banco)
	end
	if (@ve_operacion='UPDATE') begin
	update banco 
	set nom_banco = @ve_nom_banco
    where cod_banco = @ve_cod_banco
	end
	else if (@ve_operacion='DELETE') begin
	delete banco 
    where cod_banco = @ve_cod_banco
	end		
END
go