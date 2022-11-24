---------------------f_fa_cant_por_nc-----------------
CREATE FUNCTION [dbo].[f_fa_cant_por_nc] (@ve_cod_item_factura numeric, @ve_filtro varchar(20)=NULL)
RETURNS T_CANTIDAD 
AS
BEGIN

declare @cant_fa T_CANTIDAD,
		@cant_nc T_CANTIDAD,	
		@res T_CANTIDAD,
		@kl_cod_tipo_nota_credito_fa numeric,
		@kl_cod_estado_doc_sii_emitida numeric,
		@kl_cod_estado_doc_sii_impresa numeric,
		@kl_cod_estado_doc_sii_enviada numeric

	set @kl_cod_tipo_nota_credito_fa = 1  
	set @kl_cod_estado_doc_sii_emitida = 1  
	set @kl_cod_estado_doc_sii_impresa = 2  
	set @kl_cod_estado_doc_sii_enviada = 3  

	-- cantidad en factura
	select @cant_fa = isnull(sum(cantidad), 0) 
	from item_factura 
	where cod_item_factura = @ve_cod_item_factura

	-- cantidad en nc
	if (@ve_filtro = 'TODO_ESTADO')
		select @cant_nc = isnull(sum(cantidad), 0)
		from item_nota_credito itnc , nota_credito nc
		where itnc.cod_item_doc = @ve_cod_item_factura and
			  itnc.cod_nota_credito = nc.cod_nota_credito and  
			  nc.cod_tipo_nota_credito = @kl_cod_tipo_nota_credito_fa	and 
			  nc.cod_estado_doc_sii in (@kl_cod_estado_doc_sii_emitida, @kl_cod_estado_doc_sii_impresa, @kl_cod_estado_doc_sii_enviada)

	else
		select @cant_nc = isnull(sum(cantidad), 0)
		from item_nota_credito itnc , nota_credito nc
		where itnc.cod_item_doc = @ve_cod_item_factura and
			  itnc.cod_nota_credito = nc.cod_nota_credito and  
			  nc.cod_tipo_nota_credito = @kl_cod_tipo_nota_credito_fa	and 
			  nc.cod_estado_doc_sii in (@kl_cod_estado_doc_sii_impresa, @kl_cod_estado_doc_sii_enviada)



	set @res = @cant_fa - @cant_nc


return @res;
END
go
